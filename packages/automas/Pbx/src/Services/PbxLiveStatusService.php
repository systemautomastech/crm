<?php

namespace Automas\Pbx\Services;

use Automas\Pbx\Models\PbxExtension;
use Automas\Pbx\Models\PbxSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PbxLiveStatusService
{
    /**
     * Allowed status values in ERP UI.
     */
    protected const ALLOWED_STATUSES = [
        'available',
        'ringing',
        'on_call',
        'offline',
        'unknown',
    ];

    public function __construct(
        protected AmiConnectionService $amiService
    ) {}

    /**
     * Get live statuses for a collection of PbxExtension models.
     *
     * @param Collection<int, PbxExtension> $extensions
     * @return array<int, array{extension_id: int, extension: string, status: string, registered: bool, in_call: bool}>
     */
    public function getStatuses(Collection $extensions): array
    {
        if ($extensions->isEmpty()) {
            return [];
        }

        // Group extensions by created_by (creatorId / tenant workspace)
        $creatorIds = $extensions->pluck('created_by')->unique()->filter();

        // Fetch PBX settings for these creator IDs
        $settings = PbxSetting::query()
            ->whereIn('created_by', $creatorIds)
            ->where('is_enabled', true)
            ->get()
            ->keyBy('created_by');

        $results = [];
        $pbxStatusMaps = [];

        // Group requested extension numbers by creatorId for strict tenant isolation
        $erpExtensionsByCreator = [];
        foreach ($extensions as $ext) {
            $creatorId = $ext->created_by;
            $extNum = trim((string) $ext->extension);
            if ($extNum !== '') {
                $erpExtensionsByCreator[$creatorId][$extNum] = true;
            }
        }

        // Fetch live state for each unique PBX setting
        foreach ($settings as $creatorId => $setting) {
            $allowedExtensionNumbers = $erpExtensionsByCreator[$creatorId] ?? [];
            $pbxStatusMaps[$creatorId] = $this->fetchPbxLiveState($setting, $allowedExtensionNumbers);
        }

        // Map each ERP extension model to its normalized live status
        foreach ($extensions as $extension) {
            $creatorId = $extension->created_by;
            $extNumber = trim((string) $extension->extension);
            $setting = $settings->get($creatorId);

            if (!$setting || !isset($pbxStatusMaps[$creatorId])) {
                $results[$extension->id] = [
                    'extension_id' => $extension->id,
                    'extension' => $extNumber,
                    'is_active' => (bool) $extension->is_active,
                    'status' => 'unknown',
                    'registered' => false,
                    'in_call' => false,
                ];
                continue;
            }

            $pbxMap = $pbxStatusMaps[$creatorId];
            $extStatus = $pbxMap[$extNumber] ?? [
                'status' => 'unknown',
                'registered' => false,
                'in_call' => false,
            ];

            $results[$extension->id] = array_merge([
                'extension_id' => $extension->id,
                'extension' => $extNumber,
                'is_active' => (bool) $extension->is_active,
            ], $extStatus);
        }

        return $results;
    }

    /**
     * Primary live state resolution:
     * 1. Try amarSIP HTTP API if call_report_api_url & call_report_api_key are set.
     * 2. Fallback to direct AMI socket connection if HTTP API is not configured or fails.
     *
     * @param PbxSetting $setting
     * @param array<string, bool> $erpExtensionNumbers Map of allowed ERP extension numbers ['105' => true, '106' => true]
     * @return array<string, array{status: string, registered: bool, in_call: bool}>
     */
    protected function fetchPbxLiveState(PbxSetting $setting, array $erpExtensionNumbers): array
    {
        if (empty($erpExtensionNumbers)) {
            return [];
        }

        // Strategy 1: Fetch via amarSIP HTTP live-status API
        if (!empty($setting->call_report_api_url) && !empty($setting->call_report_api_key)) {
            $apiStatuses = $this->fetchViaIssabelApi($setting, $erpExtensionNumbers);
            if ($apiStatuses !== null) {
                return $apiStatuses;
            }
        }

        // Strategy 2: Fallback to direct AMI socket connection
        if (!empty($setting->ami_host) && !empty($setting->ami_port) && !empty($setting->ami_username)) {
            return $this->fetchViaAmiSocket($setting, $erpExtensionNumbers);
        }

        return [];
    }

    /**
     * Fetch live extension statuses from amarSIP HTTP live-status API.
     *
     * @param PbxSetting $setting
     * @param array<string, bool> $erpExtensionNumbers
     * @return array<string, array{status: string, registered: bool, in_call: bool}>|null Returns null on HTTP failure to trigger fallback
     */
    protected function fetchViaIssabelApi(PbxSetting $setting, array $erpExtensionNumbers): ?array
    {
        $cleanExtensions = array_values(array_unique(array_filter(array_map(
            fn($e) => trim((string) $e),
            array_keys($erpExtensionNumbers)
        ))));

        if (empty($cleanExtensions)) {
            return [];
        }

        // Construct API URL based on existing call_report_api_url format
        $baseUrl = rtrim($setting->call_report_api_url, '/');
        // If baseUrl ends with a filename like /call-summary.php or /call-logs.php, strip filename
        $endpointUrl = preg_replace('/\/[^\/]+\.php$/i', '', $baseUrl) . '/live-status.php';

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $setting->call_report_api_key,
                'Accept' => 'application/json',
            ])
                ->connectTimeout(3)
                ->timeout(5)
                ->get($endpointUrl, [
                    'extensions' => implode(',', $cleanExtensions),
                ]);

            if ($response->failed()) {
                Log::warning('amarSIP HTTP live-status API failed', [
                    'created_by' => $setting->created_by,
                    'status_code' => $response->status(),
                    'url' => $endpointUrl,
                ]);
                return null;
            }

            $data = $response->json();
            if (!is_array($data) || !($data['success'] ?? false)) {
                Log::warning('amarSIP HTTP live-status API returned invalid payload structure', [
                    'created_by' => $setting->created_by,
                    'url' => $endpointUrl,
                ]);
                return null;
            }

            $rawExtensions = $data['extensions'] ?? [];
            if (!is_array($rawExtensions)) {
                return null;
            }

            $statusMap = [];

            // Strict filtering: process ONLY requested ERP extension numbers
            foreach ($cleanExtensions as $extNum) {
                if (isset($rawExtensions[$extNum]) && is_array($rawExtensions[$extNum])) {
                    $item = $rawExtensions[$extNum];
                    $rawStatus = is_string($item['status'] ?? null) ? strtolower(trim($item['status'])) : 'unknown';

                    // Normalize status string
                    $status = in_array($rawStatus, self::ALLOWED_STATUSES, true) ? $rawStatus : 'unknown';
                    $registered = (bool) ($item['registered'] ?? ($status === 'available' || $status === 'ringing' || $status === 'on_call'));
                    $inCall = (bool) ($item['in_call'] ?? ($status === 'on_call'));

                    $statusMap[$extNum] = [
                        'status' => $status,
                        'registered' => $registered,
                        'in_call' => $inCall,
                    ];
                } else {
                    $statusMap[$extNum] = [
                        'status' => 'unknown',
                        'registered' => false,
                        'in_call' => false,
                    ];
                }
            }

            return $statusMap;
        } catch (\Throwable $e) {
            Log::warning('amarSIP HTTP live-status API request error', [
                'created_by' => $setting->created_by,
                'url' => $endpointUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Secondary provider: Direct TCP socket connection to Asterisk AMI (when AMI is remotely accessible).
     *
     * @param PbxSetting $setting
     * @param array<string, bool> $erpExtensionNumbers
     * @return array<string, array{status: string, registered: bool, in_call: bool}>
     */
    protected function fetchViaAmiSocket(PbxSetting $setting, array $erpExtensionNumbers): array
    {
        $socket = null;

        try {
            $socket = $this->amiService->connect($setting, 'off');
            stream_set_timeout($socket, 3);

            $pjsipEndpoints = $this->queryPjsipEndpoints($socket, $erpExtensionNumbers);
            $sipPeers = $this->querySipPeers($socket, $erpExtensionNumbers);
            $extStateHints = $this->queryExtensionStateList($socket, $erpExtensionNumbers);
            $channelsByExt = $this->queryCoreChannels($socket, $erpExtensionNumbers);

            $this->amiService->executeAction($socket, ['Action' => 'Logoff'], 1);

            return $this->buildStatusMap($erpExtensionNumbers, $pjsipEndpoints, $sipPeers, $extStateHints, $channelsByExt);
        } catch (\Throwable $e) {
            Log::warning('PBX live status direct AMI socket fetch failed', [
                'created_by' => $setting->created_by,
                'pbx_host' => $setting->ami_host,
                'error' => $e->getMessage(),
            ]);

            return [];
        } finally {
            if (is_resource($socket)) {
                @fclose($socket);
            }
        }
    }

    /**
     * Retrieve PJSIP endpoint states from AMI.
     */
    protected function queryPjsipEndpoints($socket, array $erpExtensionNumbers): array
    {
        $events = $this->amiService->executeAction($socket, ['Action' => 'PJSIPShowEndpoints'], 3);
        $endpoints = [];

        foreach ($events as $event) {
            $eventName = $event['Event'] ?? '';
            if ($eventName !== 'EndpointList' && $eventName !== 'EndpointDetail') {
                continue;
            }

            $objectName = trim($event['ObjectName'] ?? $event['Endpoint'] ?? '');
            if ($objectName === '' || !isset($erpExtensionNumbers[$objectName])) {
                continue;
            }

            $rawDeviceState = trim($event['DeviceState'] ?? '');
            $deviceState = strtolower($rawDeviceState);

            if ($deviceState === 'unavailable' || $deviceState === 'invalid') {
                $status = 'offline';
                $registered = false;
            } elseif ($deviceState === 'not in use') {
                $status = 'available';
                $registered = true;
            } elseif (in_array($deviceState, ['in use', 'busy'], true)) {
                $status = 'on_call';
                $registered = true;
            } elseif (str_contains($deviceState, 'ring')) {
                $status = 'ringing';
                $registered = true;
            } else {
                $status = 'unknown';
                $registered = false;
            }

            $endpoints[$objectName] = [
                'status' => $status,
                'registered' => $registered,
                'device_state' => $rawDeviceState,
            ];
        }

        return $endpoints;
    }

    /**
     * Retrieve legacy SIP peer states from AMI.
     */
    protected function querySipPeers($socket, array $erpExtensionNumbers): array
    {
        $events = $this->amiService->executeAction($socket, ['Action' => 'SIPpeers'], 3);
        $peers = [];

        foreach ($events as $event) {
            if (($event['Event'] ?? '') !== 'PeerEntry') {
                continue;
            }

            $rawName = trim($event['ObjectName'] ?? $event['Channeltype/ObjectName'] ?? '');
            if ($rawName === '') {
                continue;
            }

            $cleanName = preg_replace('/^(?:SIP|PJSIP)\//i', '', $rawName);
            $parts = explode('/', $cleanName);
            $objectName = trim($parts[0]);

            if (!isset($erpExtensionNumbers[$objectName])) {
                continue;
            }

            $ip = trim($event['IPaddress'] ?? '');
            $statusText = strtoupper(trim($event['Status'] ?? ''));

            $isUnreachable = $ip === '(Unspecified)' || $ip === '-' || $ip === ''
                || str_starts_with($statusText, 'UNREACHABLE')
                || str_starts_with($statusText, 'UNKNOWN')
                || str_starts_with($statusText, 'OFFLINE');

            $peers[$objectName] = [
                'status' => $isUnreachable ? 'offline' : 'available',
                'registered' => !$isUnreachable,
            ];
        }

        return $peers;
    }

    /**
     * Retrieve ExtensionStateList from AMI.
     */
    protected function queryExtensionStateList($socket, array $erpExtensionNumbers): array
    {
        $events = $this->amiService->executeAction($socket, ['Action' => 'ExtensionStateList'], 3);
        $states = [];

        foreach ($events as $event) {
            if (($event['Event'] ?? '') !== 'ExtensionStatus') {
                continue;
            }

            $rawExt = trim($event['Exten'] ?? '');
            if ($rawExt === '') {
                continue;
            }

            $cleanExt = preg_replace('/^(?:SIP|PJSIP|LOCAL)\//i', '', $rawExt);
            $parts = preg_split('/[\/\-\;@]/', $cleanExt);
            $ext = trim($parts[0] ?? $cleanExt);

            if (!isset($erpExtensionNumbers[$ext])) {
                continue;
            }

            $statusCode = (int) ($event['Status'] ?? -1);
            $statusText = strtoupper(trim($event['StatusText'] ?? ''));

            if ($statusCode === 4 || $statusCode === -1 || $statusText === 'UNAVAILABLE') {
                $status = 'offline';
                $registered = false;
            } elseif ($statusCode === 8 || $statusCode === 9 || str_contains($statusText, 'RINGING')) {
                $status = 'ringing';
                $registered = true;
            } elseif ($statusCode === 1 || $statusCode === 2 || str_contains($statusText, 'INUSE') || str_contains($statusText, 'BUSY')) {
                $status = 'on_call';
                $registered = true;
            } elseif ($statusCode === 0 || $statusText === 'IDLE' || $statusText === 'NOT IN USE') {
                $status = 'available';
                $registered = true;
            } else {
                $status = 'unknown';
                $registered = false;
            }

            $states[$ext] = [
                'status' => $status,
                'registered' => $registered,
            ];
        }

        return $states;
    }

    /**
     * Parse CoreShowChannels for active call channel overrides from AMI.
     */
    protected function queryCoreChannels($socket, array $erpExtensionNumbers): array
    {
        $events = $this->amiService->executeAction($socket, ['Action' => 'CoreShowChannels'], 3);
        $channels = [];

        foreach ($events as $event) {
            if (($event['Event'] ?? '') !== 'CoreShowChannel') {
                continue;
            }

            $channelName = trim($event['Channel'] ?? '');
            if ($channelName === '') {
                continue;
            }

            $ext = $this->extractExtensionFromChannel($channelName);
            if ($ext === '' || !isset($erpExtensionNumbers[$ext])) {
                continue;
            }

            $stateNum = (int) ($event['ChannelState'] ?? -1);
            $stateDesc = strtolower(trim($event['ChannelStateDesc'] ?? ''));

            $isRinging = $stateNum === 4 || $stateNum === 5 || str_contains($stateDesc, 'ring');
            $isOnCall = $stateNum === 6 || in_array($stateDesc, ['up', 'dialing', 'offhook', 'busy'], true);

            $channels[$ext][] = [
                'ringing' => $isRinging,
                'on_call' => $isOnCall,
            ];
        }

        return $channels;
    }

    /**
     * Safely extract ERP extension number from channel string.
     */
    protected function extractExtensionFromChannel(string $channelName): string
    {
        if (preg_match('/^(?:PJSIP|SIP|LOCAL)\/([A-Za-z0-9_\-]+?)(?:[-;@<]|$)/i', $channelName, $matches)) {
            $raw = $matches[1];
        } elseif (preg_match('/^([A-Za-z0-9_\-]+)@/i', $channelName, $matches)) {
            $raw = $matches[1];
        } else {
            $raw = $channelName;
        }

        $parts = preg_split('/[\/\-\;@]/', $raw);
        return trim($parts[0] ?? '');
    }

    /**
     * Build prioritized status map.
     */
    protected function buildStatusMap(
        array $erpExtensionNumbers,
        array $pjsipEndpoints,
        array $sipPeers,
        array $extStateHints,
        array $channelsByExt
    ): array {
        $statusMap = [];

        foreach (array_keys($erpExtensionNumbers) as $ext) {
            $chList = $channelsByExt[$ext] ?? [];

            $hasRingingChannel = false;
            $hasOnCallChannel = false;

            foreach ($chList as $ch) {
                if (!empty($ch['ringing'])) {
                    $hasRingingChannel = true;
                }
                if (!empty($ch['on_call'])) {
                    $hasOnCallChannel = true;
                }
            }

            if ($hasRingingChannel) {
                $statusMap[$ext] = [
                    'status' => 'ringing',
                    'registered' => true,
                    'in_call' => false,
                ];
                continue;
            }

            if ($hasOnCallChannel) {
                $statusMap[$ext] = [
                    'status' => 'on_call',
                    'registered' => true,
                    'in_call' => true,
                ];
                continue;
            }

            $baseInfo = $pjsipEndpoints[$ext] ?? $sipPeers[$ext] ?? $extStateHints[$ext] ?? null;

            if ($baseInfo !== null) {
                $baseStatus = $baseInfo['status'];
                $isRegistered = $baseInfo['registered'];

                if ($baseStatus === 'ringing') {
                    $statusMap[$ext] = [
                        'status' => 'ringing',
                        'registered' => true,
                        'in_call' => false,
                    ];
                } elseif ($baseStatus === 'on_call') {
                    $statusMap[$ext] = [
                        'status' => 'on_call',
                        'registered' => true,
                        'in_call' => true,
                    ];
                } elseif ($baseStatus === 'available') {
                    $statusMap[$ext] = [
                        'status' => 'available',
                        'registered' => true,
                        'in_call' => false,
                    ];
                } elseif ($baseStatus === 'offline') {
                    $statusMap[$ext] = [
                        'status' => 'offline',
                        'registered' => false,
                        'in_call' => false,
                    ];
                } else {
                    $statusMap[$ext] = [
                        'status' => 'unknown',
                        'registered' => $isRegistered,
                        'in_call' => false,
                    ];
                }
            } else {
                $statusMap[$ext] = [
                    'status' => 'unknown',
                    'registered' => false,
                    'in_call' => false,
                ];
            }
        }

        return $statusMap;
    }
}
