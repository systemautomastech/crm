<?php

namespace Automas\Pbx\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PbxCallReportService
{
    /*
    |--------------------------------------------------------------------------
    | Fetch Call Summary Endpoint (Native Server-side SQL Aggregation)
    |--------------------------------------------------------------------------
    */

    public function getCallSummary(
        object $setting,
        array $extensions,
        array $filters = []
    ): array {
        if (empty($setting->call_report_api_url)) {
            throw new RuntimeException('Call report API URL is not configured.');
        }

        if (empty($setting->call_report_api_key)) {
            throw new RuntimeException('Call report API key is not configured.');
        }

        $cleanExtensions = array_values(array_unique(array_filter(array_map(
            fn($e) => trim((string) $e),
            $extensions
        ))));

        if (empty($cleanExtensions)) {
            return [
                'success' => true,
                'summary' => $this->emptySummary(),
                'status_breakdown' => [],
                'direction_breakdown' => [],
                'daily' => [],
                'hourly' => [],
                'extensions' => [],
            ];
        }

        $query = [
            'extensions' => implode(',', $cleanExtensions),
        ];

        /*
        |--------------------------------------------------------------------------
        | Date Normalization (Strictly YYYY-MM-DD for API stability)
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['from'])) {
            try {
                $query['from'] = Carbon::parse($filters['from'])->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore invalid date string
            }
        }

        if (!empty($filters['to'])) {
            try {
                $query['to'] = Carbon::parse($filters['to'])->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore invalid date string
            }
        }

        if (!empty($filters['direction'])) {
            $dir = strtolower(trim((string) $filters['direction']));
            if ($dir !== 'all' && $dir !== '') {
                $resolvedDir = in_array($dir, ['inbound', 'incoming'], true) ? 'inbound' : (in_array($dir, ['outbound', 'outgoing'], true) ? 'outbound' : $dir);
                $query['direction'] = $resolvedDir;
                $query['call_direction'] = $resolvedDir;
            }
        }

        if (!empty($filters['status'])) {
            $statusVal = trim((string) $filters['status']);
            if (strtolower($statusVal) !== 'all' && $statusVal !== '') {
                $query['status'] = $statusVal;
                $query['disposition'] = $statusVal;
                $query['call_status'] = $statusVal;
            }
        }

        if (!empty($filters['search'])) {
            $query['search'] = trim((string) $filters['search']);
        }

        $url = rtrim($setting->call_report_api_url, '/') . '/call-summary.php';

        $startTime = microtime(true);

        Log::info('amarSIP call summary request', [
            'url' => $url,
            'extensions' => $query['extensions'],
            'from' => $query['from'] ?? null,
            'to' => $query['to'] ?? null,
        ]);

        $response = Http::withHeaders([
            'X-API-Key' => $setting->call_report_api_key,
            'Accept' => 'application/json',
        ])
            ->connectTimeout(5)
            ->timeout(60)
            ->retry(1, 200)
            ->get($url, $query);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        if ($response->failed()) {
            Log::error('amarSIP call summary API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'duration_ms' => $durationMs,
            ]);

            throw new RuntimeException(
                'amarSIP call summary API failed with HTTP ' . $response->status()
            );
        }

        $data = $response->json();

        if (!is_array($data) || !($data['success'] ?? false)) {
            throw new RuntimeException(
                $data['message'] ?? 'Invalid response from amarSIP call summary API.'
            );
        }

        if (isset($data['summary']) && is_array($data['summary'])) {
            $data['summary'] = array_merge($this->emptySummary(), $data['summary']);
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch Paginated Call Logs (Server-side Paginated)
    |--------------------------------------------------------------------------
    */

    public function getCallLogs(
        object $setting,
        array $extensions,
        array $filters = []
    ): array {
        if (empty($setting->call_report_api_url)) {
            throw new RuntimeException('Call report API URL is not configured.');
        }

        if (empty($setting->call_report_api_key)) {
            throw new RuntimeException('Call report API key is not configured.');
        }

        $cleanExtensions = array_values(array_unique(array_filter(array_map(
            fn($e) => trim((string) $e),
            $extensions
        ))));

        if (empty($cleanExtensions)) {
            return $this->emptyResult($filters);
        }

        $query = [
            'extensions' => implode(',', $cleanExtensions),
            'page' => max((int) ($filters['page'] ?? 1), 1),
            'per_page' => min(max((int) ($filters['per_page'] ?? 10), 10), 100),
        ];

        if (!empty($filters['search'])) {
            $s = trim((string) $filters['search']);
            $query['search'] = $s;
            $query['number'] = $s;
            $query['phone'] = $s;
            $query['q'] = $s;
            $query['src'] = $s;
            $query['dst'] = $s;

            $digits = preg_replace('/\D+/', '', $s);
            if (strlen($digits) >= 4) {
                $query['digits'] = $digits;
                if (str_starts_with($digits, '880')) {
                    $query['search_alt'] = '0' . substr($digits, 3);
                } elseif (str_starts_with($digits, '0')) {
                    $query['search_alt'] = '880' . substr($digits, 1);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Date Normalization (Strictly YYYY-MM-DD)
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['from'])) {
            try {
                $query['from'] = Carbon::parse($filters['from'])->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (!empty($filters['to'])) {
            try {
                $query['to'] = Carbon::parse($filters['to'])->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (!empty($filters['direction'])) {
            $dir = strtolower(trim((string) $filters['direction']));
            if ($dir !== 'all' && $dir !== '') {
                if (in_array($dir, ['inbound', 'incoming'], true)) {
                    $resolvedDir = 'inbound';
                } elseif (in_array($dir, ['outbound', 'outgoing'], true)) {
                    $resolvedDir = 'outbound';
                } else {
                    $resolvedDir = $dir;
                }
                $query['direction'] = $resolvedDir;
                $query['call_direction'] = $resolvedDir;
            }
        }

        if (!empty($filters['status'])) {
            $statusVal = trim((string) $filters['status']);
            if (strtolower($statusVal) !== 'all' && $statusVal !== '') {
                $query['status'] = $statusVal;
                $query['disposition'] = $statusVal;
                $query['call_status'] = $statusVal;

                if (strtoupper($statusVal) === 'NO ANSWER') {
                    $query['disposition_alt'] = 'NOANSWER';
                } elseif (strtoupper($statusVal) === 'NOANSWER') {
                    $query['disposition_alt'] = 'NO ANSWER';
                }
            }
        }

        $url = rtrim($setting->call_report_api_url, '/') . '/call-logs.php';

        $response = $this->fetchCallLogsPage(
            $url,
            $query,
            [
                'X-API-Key' => $setting->call_report_api_key,
                'Accept' => 'application/json',
            ]
        );

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        /*
        |--------------------------------------------------------------------------
        | Fallback Safeguard Filtering
        |--------------------------------------------------------------------------
        | Ensures that returned records strictly match search, direction, and status,
        | even if the remote API did not perform strict SQL filtering.
        |
        */
        if (!empty($filters['search'])) {
            $searchTerm = trim((string) $filters['search']);
            $searchLower = strtolower($searchTerm);
            $searchDigits = preg_replace('/\D+/', '', $searchTerm);
            $searchLast7 = strlen($searchDigits) >= 7 ? substr($searchDigits, -7) : $searchDigits;

            $data = array_values(array_filter($data, function ($row) use ($searchLower, $searchDigits, $searchLast7) {
                $num = (string) ($row['number'] ?? '');
                $did = (string) ($row['did'] ?? '');
                $ext = (string) ($row['extension'] ?? '');
                $userName = (string) ($row['user_name'] ?? '');
                $src = (string) ($row['src'] ?? '');
                $dst = (string) ($row['dst'] ?? '');
                $clid = (string) ($row['clid'] ?? '');

                // 1. Direct text search
                if (
                    ($num !== '' && stripos($num, $searchLower) !== false) ||
                    ($did !== '' && stripos($did, $searchLower) !== false) ||
                    ($ext !== '' && stripos($ext, $searchLower) !== false) ||
                    ($userName !== '' && stripos($userName, $searchLower) !== false) ||
                    ($src !== '' && stripos($src, $searchLower) !== false) ||
                    ($dst !== '' && stripos($dst, $searchLower) !== false) ||
                    ($clid !== '' && stripos($clid, $searchLower) !== false)
                ) {
                    return true;
                }

                // 2. Phone digit normalization search
                if ($searchDigits !== '' && strlen($searchDigits) >= 4) {
                    $rowDigits = preg_replace('/\D+/', '', $num . ' ' . $did . ' ' . $ext . ' ' . $src . ' ' . $dst . ' ' . $clid);
                    if ($rowDigits !== '') {
                        if (
                            str_contains($rowDigits, $searchDigits) ||
                            str_contains($searchDigits, $rowDigits) ||
                            ($searchLast7 !== '' && str_contains($rowDigits, $searchLast7))
                        ) {
                            return true;
                        }
                    }
                }

                return false;
            }));
        }

        if (!empty($filters['direction'])) {
            $targetDir = strtolower(trim((string) $filters['direction']));
            if ($targetDir !== 'all' && $targetDir !== '') {
                $data = array_values(array_filter($data, function ($row) use ($targetDir) {
                    $rowDir = strtolower(trim((string) ($row['direction'] ?? '')));
                    if ($targetDir === 'inbound' || $targetDir === 'incoming') {
                        return in_array($rowDir, ['inbound', 'incoming'], true);
                    }
                    if ($targetDir === 'outbound' || $targetDir === 'outgoing') {
                        return in_array($rowDir, ['outbound', 'outgoing'], true);
                    }
                    return $rowDir === $targetDir;
                }));
            }
        }

        if (!empty($filters['status'])) {
            $targetStatus = strtoupper(trim((string) $filters['status']));
            if ($targetStatus !== 'ALL' && $targetStatus !== '') {
                $data = array_values(array_filter($data, function ($row) use ($targetStatus) {
                    $rowStatus = strtoupper(trim((string) ($row['status'] ?? '')));
                    if ($targetStatus === 'NO ANSWER' || $targetStatus === 'NOANSWER') {
                        return $rowStatus === 'NO ANSWER' || $rowStatus === 'NOANSWER';
                    }
                    return $rowStatus === $targetStatus;
                }));
            }
        }

        $pagination = $response['pagination'] ?? [];

        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => max((int) ($pagination['page'] ?? $query['page']), 1),
                'per_page' => max((int) ($pagination['per_page'] ?? $query['per_page']), 1),
                'total' => max((int) ($pagination['total'] ?? count($data)), 0),
                'last_page' => max((int) ($pagination['last_page'] ?? 1), 1),
            ],
        ];
    }

    private function fetchCallLogsPage(
        string $url,
        array $query,
        array $headers
    ): array {
        Log::info('amarSIP call report request', [
            'extensions' => $query['extensions'] ?? null,
            'from' => $query['from'] ?? null,
            'to' => $query['to'] ?? null,
            'direction' => $query['direction'] ?? null,
            'status' => $query['status'] ?? null,
            'search' => $query['search'] ?? null,
            'page' => $query['page'] ?? null,
            'per_page' => $query['per_page'] ?? null,
        ]);

        $response = Http::withHeaders($headers)
            ->connectTimeout(5)
            ->timeout(60)
            ->retry(1, 200)
            ->get($url, $query);

        if ($response->failed()) {
            Log::error('amarSIP call report API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'request_params' => $query,
                'url' => $url,
            ]);

            throw new RuntimeException(
                'amarSIP call report API failed with HTTP ' . $response->status()
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON response from amarSIP call report API.');
        }

        if (!($data['success'] ?? false)) {
            throw new RuntimeException($data['message'] ?? 'Invalid response from amarSIP call report API.');
        }

        return $data;
    }

    public function emptyResult(array $filters = []): array
    {
        return [
            'success' => true,
            'data' => [],
            'pagination' => [
                'page' => 1,
                'per_page' => (int) ($filters['per_page'] ?? 10),
                'total' => 0,
                'last_page' => 1,
            ],
        ];
    }

    public function emptySummary(): array
    {
        return [
            'total_calls' => 0,
            'answered_calls' => 0,
            'no_answer_calls' => 0,
            'busy_calls' => 0,
            'failed_calls' => 0,
            'congestion_calls' => 0,
            'answer_rate' => 0.0,
            'miss_rate' => 0.0,

            'inbound_calls' => 0,
            'inbound_answered' => 0,
            'inbound_missed' => 0,
            'inbound_failed' => 0,
            'inbound_answer_rate' => 0.0,
            'inbound_duration' => 0,
            'inbound_talk_time' => 0,
            'inbound_ring_time' => 0,
            'inbound_avg_duration' => 0,
            'inbound_avg_talk_time' => 0,
            'inbound_avg_ring_time' => 0,

            'outbound_calls' => 0,
            'outbound_answered' => 0,
            'outbound_unanswered' => 0,
            'outbound_failed' => 0,
            'outbound_answer_rate' => 0.0,
            'outbound_duration' => 0,
            'outbound_talk_time' => 0,
            'outbound_ring_time' => 0,
            'outbound_avg_duration' => 0,
            'outbound_avg_talk_time' => 0,
            'outbound_avg_ring_time' => 0,

            'total_duration' => 0,
            'total_talk_time' => 0,
            'total_ring_time' => 0,
            'average_duration' => 0,
            'average_talk_time' => 0,

            'duration_brackets' => [
                'under_30s' => 0,
                'from_30s_to_1m' => 0,
                'from_1m_to_5m' => 0,
                'from_5m_to_15m' => 0,
                'over_15m' => 0,
            ],

            'recording_count' => 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Chart Visualization Aggregation from call-summary.php Response
    |--------------------------------------------------------------------------
    */

    public function buildChartsDataFromSummary(array $summaryResult, array $extensionsList = [], ?string $selectedExtension = null): array
    {
        $summary = array_merge($this->emptySummary(), $summaryResult['summary'] ?? []);
        $daily = $summaryResult['daily'] ?? [];
        $hourly = $summaryResult['hourly'] ?? [];
        $extBreakdown = $summaryResult['extensions'] ?? [];

        // Index system extensions by extension number
        $knownExtensions = [];
        foreach ($extensionsList as $extObj) {
            $extNum = is_array($extObj) ? ($extObj['extension'] ?? '') : (string) ($extObj->extension ?? '');
            if ($extNum !== '') {
                $knownExtensions[(string)$extNum] = $extObj;
            }
        }

        // 1. Call Direction Breakdown
        $directionChart = [
            [
                'name' => 'Inbound',
                'value' => (int) ($summary['inbound_calls'] ?? 0),
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Outbound',
                'value' => (int) ($summary['outbound_calls'] ?? 0),
                'color' => '#8b5cf6',
            ],
        ];

        // 2. Call Status Breakdown
        $statusChart = [
            [
                'name' => 'Answered',
                'value' => (int) ($summary['answered_calls'] ?? 0),
                'color' => '#10b981',
            ],
            [
                'name' => 'No Answer',
                'value' => (int) ($summary['no_answer_calls'] ?? 0),
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Busy',
                'value' => (int) ($summary['busy_calls'] ?? 0),
                'color' => '#f97316',
            ],
            [
                'name' => 'Failed',
                'value' => (int) ($summary['failed_calls'] ?? 0),
                'color' => '#f43f5e',
            ],
            [
                'name' => 'Congestion',
                'value' => (int) ($summary['congestion_calls'] ?? 0),
                'color' => '#6b7280',
            ],
        ];

        // 3. Extension Aggregations & Performance
        $extDataMap = [];
        foreach ($extBreakdown as $e) {
            $extNum = (string) $e['extension'];
            $extDataMap[$extNum] = $e;
        }

        $allExtNums = array_unique(array_merge(array_keys($knownExtensions), array_keys($extDataMap)));
        if (!empty($selectedExtension) && strtolower($selectedExtension) !== 'all') {
            $extFilter = (string) $selectedExtension;
            $allExtNums = array_values(array_filter($allExtNums, fn($ext) => (string)$ext === $extFilter));
        }

        $topExtensionsList = [];
        $extensionPerformanceList = [];

        foreach ($allExtNums as $extNum) {
            $extInfo = $extDataMap[$extNum] ?? [
                'extension' => $extNum,
                'total' => 0,
                'answered' => 0,
                'missed' => 0,
                'failed' => 0,
                'inbound' => 0,
                'outbound' => 0,
                'talk_time' => 0,
            ];

            $extData = $knownExtensions[$extNum] ?? null;
            $uName = is_array($extData) ? ($extData['user_name'] ?? "Extension {$extNum}") : ($extData->user?->name ?? "Extension {$extNum}");
            $uAvatar = is_array($extData) ? ($extData['avatar'] ?? '') : ($extData->user?->avatar ?? '');

            $totalCalls = (int) ($extInfo['total'] ?? 0);
            $answeredCalls = (int) ($extInfo['answered'] ?? 0);
            $missed = (int) ($extInfo['missed'] ?? 0);
            $failed = (int) ($extInfo['failed'] ?? 0);
            $inbound = (int) ($extInfo['inbound'] ?? 0);
            $outbound = (int) ($extInfo['outbound'] ?? 0);
            $talkTime = (int) ($extInfo['talk_time'] ?? 0);

            $ansRate = $totalCalls > 0 ? round(($answeredCalls / $totalCalls) * 100, 1) : 0;
            $avgTalk = $answeredCalls > 0 ? round($talkTime / $answeredCalls) : 0;
            $missedPct = $totalCalls > 0 ? round(($missed / $totalCalls) * 100, 1) : 0;
            $failedPct = $totalCalls > 0 ? round(($failed / $totalCalls) * 100, 1) : 0;

            $topExtensionsList[] = [
                'extension' => 'Ext ' . $extNum,
                'total' => $totalCalls,
                'answered' => $answeredCalls,
                'duration' => $talkTime,
            ];

            $extensionPerformanceList[] = [
                'extension' => $extNum,
                'user_name' => $uName,
                'avatar' => $uAvatar,
                'department' => 'PBX Extension',
                'totalCalls' => $totalCalls,
                'answeredCalls' => $answeredCalls,
                'answerRate' => $ansRate,
                'avgTalkTime' => (int) $avgTalk,
                'missed' => $missed,
                'missedPercent' => $missedPct,
                'failed' => $failed,
                'failedPercent' => $failedPct,
                'inbound' => $inbound,
                'outbound' => $outbound,
                'talkTime' => $talkTime,
                'status' => 'unknown',
            ];
        }

        usort($topExtensionsList, fn($a, $b) => $b['total'] <=> $a['total']);
        $topExtensions = array_values(array_slice($topExtensionsList, 0, 8));

        usort($extensionPerformanceList, fn($a, $b) => $b['totalCalls'] <=> $a['totalCalls']);

        // 4. Daily Trends & Day-of-Week Peak Calculations
        $trendData = [];
        $dayOfWeekStats = [
            'Mon' => ['day' => 'Monday', 'short' => 'Mon', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Tue' => ['day' => 'Tuesday', 'short' => 'Tue', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Wed' => ['day' => 'Wednesday', 'short' => 'Wed', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Thu' => ['day' => 'Thursday', 'short' => 'Thu', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Fri' => ['day' => 'Friday', 'short' => 'Fri', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Sat' => ['day' => 'Saturday', 'short' => 'Sat', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
            'Sun' => ['day' => 'Sunday', 'short' => 'Sun', 'total' => 0, 'answered' => 0, 'answerRate' => 0],
        ];

        $maxDailyTotal = 0;
        $maxDailyAnswerRate = 0.0;

        foreach ($daily as $d) {
            $timestamp = strtotime($d['date']);
            $formattedDate = date('M d', $timestamp);
            $dayOfWeekKey = date('D', $timestamp);
            $dayFullName = date('l', $timestamp);
            $tot = (int) ($d['total'] ?? 0);
            $ans = (int) ($d['answered'] ?? 0);
            $miss = (int) ($d['missed'] ?? 0);
            $ansRate = $tot > 0 ? round(($ans / $tot) * 100, 1) : 0;

            if ($tot > $maxDailyTotal) {
                $maxDailyTotal = $tot;
            }
            if ($tot >= 3 && $ansRate > $maxDailyAnswerRate) {
                $maxDailyAnswerRate = $ansRate;
            }

            if (isset($dayOfWeekStats[$dayOfWeekKey])) {
                $dayOfWeekStats[$dayOfWeekKey]['total'] += $tot;
                $dayOfWeekStats[$dayOfWeekKey]['answered'] += $ans;
            }

            $trendData[] = [
                'fullDate' => $d['date'],
                'date' => $formattedDate,
                'dayOfWeek' => $dayOfWeekKey,
                'dayFullName' => $dayFullName,
                'total' => $tot,
                'answered' => $ans,
                'missed' => $miss,
                'answerRate' => $ansRate,
                'isPeak' => false,
                'isPeakAnswer' => false,
            ];
        }

        // Calculate answer rates for day of week stats
        $maxDayOfWeekTotal = 0;
        $maxDayOfWeekAnswerRate = 0.0;
        $peakDayOfWeek = null;
        $peakAnswerDayOfWeek = null;

        foreach ($dayOfWeekStats as &$dow) {
            $dow['answerRate'] = $dow['total'] > 0 ? round(($dow['answered'] / $dow['total']) * 100, 1) : 0;
            if ($dow['total'] > $maxDayOfWeekTotal) {
                $maxDayOfWeekTotal = $dow['total'];
                $peakDayOfWeek = $dow['day'];
            }
            if ($dow['total'] > 0 && $dow['answerRate'] > $maxDayOfWeekAnswerRate) {
                $maxDayOfWeekAnswerRate = $dow['answerRate'];
                $peakAnswerDayOfWeek = $dow['day'];
            }
        }
        unset($dow);

        // Mark peak days in daily trend array
        foreach ($trendData as &$td) {
            if ($maxDailyTotal > 0 && $td['total'] === $maxDailyTotal) {
                $td['isPeak'] = true;
            }
            if ($maxDailyAnswerRate > 0 && $td['answerRate'] === $maxDailyAnswerRate && $td['total'] > 0) {
                $td['isPeakAnswer'] = true;
            }
        }
        unset($td);

        $dayOfWeekList = array_values($dayOfWeekStats);

        // 5. Hourly Trends (Full 24 Hours: 12 AM to 11 PM)
        $hourlyMap = [];
        for ($h = 0; $h < 24; $h++) {
            $timeLabel = date('g A', strtotime("{$h}:00"));
            $hourlyMap[$h] = [
                'hour' => $h,
                'time' => $timeLabel,
                'total' => 0,
                'answered' => 0,
                'answerRate' => 0,
                'isPeak' => false,
                'isPeakAnswer' => false,
            ];
        }

        foreach ($hourly as $hItem) {
            $hourNum = (int) ($hItem['hour'] ?? 0);
            if (isset($hourlyMap[$hourNum])) {
                $hourlyMap[$hourNum]['total'] += (int) ($hItem['total'] ?? 0);
                $hourlyMap[$hourNum]['answered'] += (int) ($hItem['answered'] ?? 0);
            }
        }

        $maxHourlyTotal = 0;
        $maxHourlyAnswerRate = 0.0;

        foreach ($hourlyMap as &$item) {
            $item['answerRate'] = $item['total'] > 0 ? round(($item['answered'] / $item['total']) * 100, 1) : 0;
            if ($item['total'] > $maxHourlyTotal) {
                $maxHourlyTotal = $item['total'];
            }
            if ($item['total'] >= 3 && $item['answerRate'] > $maxHourlyAnswerRate) {
                $maxHourlyAnswerRate = $item['answerRate'];
            }
        }
        unset($item);

        if ($maxHourlyAnswerRate === 0.0) {
            foreach ($hourlyMap as $item) {
                if ($item['total'] > 0 && $item['answerRate'] > $maxHourlyAnswerRate) {
                    $maxHourlyAnswerRate = $item['answerRate'];
                }
            }
        }

        $peakHourLabel = null;
        $peakAnswerHourLabel = null;

        foreach ($hourlyMap as &$item) {
            if ($maxHourlyTotal > 0 && $item['total'] === $maxHourlyTotal) {
                $item['isPeak'] = true;
                $peakHourLabel = $item['time'];
            }
            if ($maxHourlyAnswerRate > 0 && $item['answerRate'] === $maxHourlyAnswerRate && $item['total'] > 0) {
                $item['isPeakAnswer'] = true;
                $peakAnswerHourLabel = $item['time'];
            }
        }
        unset($item);

        $hourlyTrendData = array_values($hourlyMap);

        // 6. Live Status Aggregations (Summary does not fabricate live status; live status is polled via Asterisk AMI)
        $totalExtCount = max(count($allExtNums), 1);
        $liveStatus = [
            'online' => 0,
            'onlinePercent' => 0.0,
            'onCall' => 0,
            'onCallPercent' => 0.0,
            'ringing' => 0,
            'ringingPercent' => 0.0,
            'offline' => 0,
            'offlinePercent' => 0.0,
            'activeExtensions' => 0,
            'totalExtensions' => $totalExtCount,
            'utilizationPercent' => 0.0,
        ];

        return [
            'direction' => $directionChart,
            'status' => $statusChart,
            'extensions' => $topExtensions,
            'trend' => $trendData,
            'hourlyTrend' => $hourlyTrendData,
            'dayOfWeekTrend' => $dayOfWeekList,
            'extensionPerformance' => $extensionPerformanceList,
            'liveStatus' => $liveStatus,
            'durationBrackets' => $summary['duration_brackets'] ?? [
                'under_30s' => 0,
                'from_30s_to_1m' => 0,
                'from_1m_to_5m' => 0,
                'from_5m_to_15m' => 0,
                'over_15m' => 0,
            ],
            'peaks' => [
                'peakHour' => $peakHourLabel,
                'peakHourVolume' => $maxHourlyTotal,
                'peakAnswerHour' => $peakAnswerHourLabel,
                'peakHourAnswerRate' => $maxHourlyAnswerRate,
                'peakDayOfWeek' => $peakDayOfWeek,
                'peakDayVolume' => $maxDayOfWeekTotal,
                'peakAnswerDayOfWeek' => $peakAnswerDayOfWeek,
                'peakDayAnswerRate' => $maxDayOfWeekAnswerRate,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Centralized Report Period Resolver
    |--------------------------------------------------------------------------
    |
    | Resolves report period and enforces application timezone consistency.
    | Default period is strictly 'today'.
    | 'all_time' returns null dates and is NEVER a default or fallback.
    |
    */

    public function resolveReportPeriod(
        ?string $period = null,
        ?string $customFrom = null,
        ?string $customTo = null,
        ?string $dateRange = null
    ): array {
        $tz = config('app.timezone') ?: 'UTC';
        $today = Carbon::today($tz)->format('Y-m-d');
        $now = Carbon::now($tz);

        $period = strtolower(trim((string) $period));

        // 1. If date_range is provided and not 'all time', extract custom from and to
        if (!empty($dateRange) && strcasecmp(trim($dateRange), 'all time') !== 0) {
            $parts = preg_split('/(?:\s+-\s+|\s+to\s+)/i', trim($dateRange));
            if (is_array($parts) && count($parts) === 2) {
                $parsedFrom = trim($parts[0]);
                $parsedTo = trim($parts[1]);
                if (!empty($parsedFrom) && !empty($parsedTo)) {
                    $customFrom = $parsedFrom;
                    $customTo = $parsedTo;
                }
            }
        }

        // 2. If explicit custom dates exist (or period is 'custom') and period is not 'all_time'
        if (($period === 'custom' || (!empty($customFrom) && !empty($customTo))) && $period !== 'all_time') {
            try {
                $fromDate = Carbon::parse($customFrom, $tz)->startOfDay();
                $toDate = Carbon::parse($customTo, $tz)->startOfDay();

                if ($fromDate->lte($toDate)) {
                    $formattedFrom = $fromDate->format('Y-m-d');
                    $formattedTo = $toDate->format('Y-m-d');

                    return [
                        'period' => 'custom',
                        'from' => $formattedFrom,
                        'to' => $formattedTo,
                        'date_range' => "{$formattedFrom} - {$formattedTo}",
                    ];
                }
            } catch (\Throwable $e) {
                // Invalid dates, fallback to standard period resolution below
            }
        }

        // 3. Resolve predefined periods
        switch ($period) {
            case 'this_week':
                $startOfWeek = $now->copy()->startOfWeek()->format('Y-m-d');
                return [
                    'period' => 'this_week',
                    'from' => $startOfWeek,
                    'to' => $today,
                    'date_range' => "{$startOfWeek} - {$today}",
                ];

            case 'this_month':
                $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
                return [
                    'period' => 'this_month',
                    'from' => $startOfMonth,
                    'to' => $today,
                    'date_range' => "{$startOfMonth} - {$today}",
                ];

            case 'previous_month':
                $prevMonth = $now->copy()->startOfMonth()->subMonth();
                $startOfPrevMonth = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
                $endOfPrevMonth = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
                return [
                    'period' => 'previous_month',
                    'from' => $startOfPrevMonth,
                    'to' => $endOfPrevMonth,
                    'date_range' => "{$startOfPrevMonth} - {$endOfPrevMonth}",
                ];

            case 'all_time':
                return [
                    'period' => 'all_time',
                    'from' => null,
                    'to' => null,
                    'date_range' => 'All Time',
                ];

            case 'today':
            default:
                // Default is strictly 'today', NEVER All Time
                return [
                    'period' => 'today',
                    'from' => $today,
                    'to' => $today,
                    'date_range' => "{$today} - {$today}",
                ];
        }
    }
}

