<?php

namespace Automas\Pbx\Services;

use Automas\Pbx\Models\PbxSetting;
use Illuminate\Support\Facades\Log;

class AmiConnectionService
{
    public function connect(PbxSetting $setting, string $events = 'off')
    {
        $socket = @fsockopen(
            $setting->ami_host,
            $setting->ami_port,
            $errno,
            $errstr,
            10
        );

        if (!$socket) {
            throw new \RuntimeException("AMI connection failed for workspace {$setting->created_by}: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 30);
        stream_set_blocking($socket, true);

        $this->write($socket, [
            'Action' => 'Login',
            'Username' => $setting->ami_username,
            'Secret' => $setting->ami_password,
            'Events' => $events,
        ]);

        $response = $this->readResponse($socket);

        if (($response['Response'] ?? '') !== 'Success') {
            fclose($socket);
            throw new \RuntimeException("AMI login failed for workspace {$setting->created_by}: " . ($response['Message'] ?? 'Unknown error'));
        }

        return $socket;
    }

    public function originate(PbxSetting $setting, string $agentExtension, string $phone): bool
    {
        $socket = null;

        try {
            $socket = @fsockopen($setting->ami_host, $setting->ami_port, $errno, $errstr, 10);

            if (!$socket) {
                Log::error('PBX AMI originate connection failed', [
                    'created_by' => $setting->created_by,
                    'error' => "{$errstr} ({$errno})",
                ]);
                return false;
            }

            stream_set_timeout($socket, 5);

            $this->write($socket, [
                'Action' => 'Login',
                'Username' => $setting->ami_username,
                'Secret' => $setting->ami_password,
                'Events' => 'off',
            ]);

            $this->readResponse($socket);

            $this->write($socket, [
                'Action' => 'Originate',
                'Channel' => "SIP/{$agentExtension}",
                'Context' => 'from-internal',
                'Exten' => $phone,
                'Priority' => '1',
                'CallerID' => $agentExtension,
                'Async' => 'true',
            ]);

            $response = $this->readResponse($socket);

            $this->write($socket, ['Action' => 'Logoff']);

            return ($response['Response'] ?? '') === 'Success';
        } catch (\Throwable $e) {
            Log::error('PBX AMI originate failed', [
                'created_by' => $setting->created_by,
                'message' => $e->getMessage(),
            ]);
            return false;
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    public function parseEvent(string $raw): array
    {
        $event = [];

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);

            if ($line === '' || !str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $event[trim($key)] = trim($value);
        }

        return $event;
    }

    public function readEvent($socket): ?array
    {
        $buffer = '';

        while (!feof($socket)) {
            $line = fgets($socket);

            if ($line === false) {
                break;
            }

            $buffer .= $line;

            if (trim($line) === '') {
                if (trim($buffer) === '') {
                    continue;
                }

                return $this->parseEvent($buffer);
            }
        }

        return null;
    }

    public function executeAction($socket, array $payload, int $timeoutSeconds = 3): array
    {
        if (!is_resource($socket)) {
            return [];
        }

        if (!isset($payload['ActionID'])) {
            $payload['ActionID'] = 'req_' . uniqid();
        }

        $actionId = $payload['ActionID'];
        $this->write($socket, $payload);

        $events = [];
        $startTime = microtime(true);

        while (is_resource($socket) && !feof($socket) && (microtime(true) - $startTime) < $timeoutSeconds) {
            $event = $this->readEvent($socket);

            if ($event === null) {
                break;
            }

            if (isset($event['ActionID']) && $event['ActionID'] !== $actionId) {
                continue;
            }

            $events[] = $event;

            if (isset($event['Response']) && strtolower($event['Response']) === 'error') {
                break;
            }

            $eventName = strtolower($event['Event'] ?? '');
            if (
                str_ends_with($eventName, 'complete') ||
                str_ends_with($eventName, 'completes') ||
                ($eventName === '' && isset($event['Response']) && strtolower($event['Response']) === 'success' && !isset($event['EventList']))
            ) {
                break;
            }
        }

        return $events;
    }

    public function write($socket, array $payload): void
    {
        $message = '';

        foreach ($payload as $key => $value) {
            $message .= "{$key}: {$value}\r\n";
        }

        $message .= "\r\n";

        fwrite($socket, $message);
    }

    protected function readResponse($socket): array
    {
        $buffer = '';

        while (!feof($socket)) {
            $line = fgets($socket);

            if ($line === false) {
                break;
            }

            $buffer .= $line;

            if (trim($line) === '') {
                break;
            }
        }

        return $this->parseEvent($buffer);
    }
}

