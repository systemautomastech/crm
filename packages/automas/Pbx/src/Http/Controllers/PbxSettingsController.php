<?php

namespace Automas\Pbx\Http\Controllers;

use Automas\Pbx\Models\PbxSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PbxSettingsController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if (!Auth::user()->can('manage settings')) {
            return redirect()
                ->back()
                ->with('error', __('Permission denied.'));
        }

        $creatorId = (int) creatorId();

        $setting = PbxSetting::query()
            ->forCreator($creatorId)
            ->first();

        return Inertia::render('Pbx/settings/Index', [
            'setting' => $setting,
            'availableRingtones' => $this->getAvailableRingtones(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Auth::user()->can('manage settings')) {
            return redirect()
                ->back()
                ->with('error', __('Permission denied.'));
        }

        $creatorId = (int) creatorId();

        $validated = $this->validateRequest($request);

        $validated['created_by'] = $creatorId;
        $validated['is_enabled'] = $request->boolean('is_enabled');

        /*
         * When editing, an empty password means:
         * keep the existing AMI password.
         */
        if (empty($validated['ami_password'])) {
            unset($validated['ami_password']);
        }

        PbxSetting::query()->updateOrCreate(
            [
                'created_by' => $creatorId,
            ],
            $validated
        );

        return redirect()
            ->back()
            ->with('success', __('PBX settings saved successfully.'));
    }

    public function update(Request $request): RedirectResponse
    {
        return $this->store($request);
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'pbx_name' => ['required', 'string', 'max:191'],
            'pbx_host' => ['nullable', 'string', 'max:191'],

            'ami_host' => ['required', 'string', 'max:191'],
            'ami_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ami_username' => ['required', 'string', 'max:191'],
            'ami_password' => ['nullable', 'string', 'max:500'],

            'sip_domain' => ['required', 'string', 'max:191'],
            'websocket_url' => ['required', 'string', 'max:500'],
            'stun_server' => ['nullable', 'string', 'max:500'],
            'sip_trunk_name' => ['nullable', 'string', 'max:191'],
            'call_report_api_url' => ['nullable', 'string', 'max:500'],
            'call_report_api_key' => ['nullable', 'string', 'max:500'],

            'extension_start' => ['required', 'integer', 'min:1'],
            'extension_end' => [
                'required',
                'integer',
                'gte:extension_start',
            ],
            'max_extensions' => ['required', 'integer', 'min:1'],

            'is_enabled' => ['nullable', 'boolean'],
            'ringtone' => ['nullable', 'string', 'max:191'],
        ]);
    }

    public function ringtone(Request $request)
    {
        $user = Auth::user();
        $creatorId = (int) creatorId();

        $setting = PbxSetting::query()
            ->forCreator($creatorId)
            ->first();

        $requestedFile = $request->query('file');
        $selectedRingtone = $requestedFile
            ? basename($requestedFile)
            : ($setting?->ringtone ?? 'ringtone.mp3');

        $candidateDirectories = [
            storage_path('app/sounds'),
            storage_path('app/sounds/ringtones'),
            storage_path('app/public/sounds'),
            storage_path('app/public/sounds/ringtones'),
            public_path('sounds'),
            public_path('sounds/ringtones'),
        ];

        $absolutePath = null;

        foreach ($candidateDirectories as $dir) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $selectedRingtone;
            if (file_exists($filePath)) {
                $absolutePath = $filePath;
                break;
            }
        }

        if (!$absolutePath) {
            $fallbacks = ['ringtone.mp3', 'ringtone1.mp3', 'default.mp3'];
            foreach ($candidateDirectories as $dir) {
                foreach ($fallbacks as $fb) {
                    $filePath = $dir . DIRECTORY_SEPARATOR . $fb;
                    if (file_exists($filePath)) {
                        $absolutePath = $filePath;
                        break 2;
                    }
                }
            }
        }

        if (!$absolutePath || !file_exists($absolutePath)) {
            abort(404, 'Ringtone not found.');
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            default => 'audio/mpeg',
        };

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    protected function getAvailableRingtones(): array
    {
        $directories = [
            storage_path('app/sounds'),
            storage_path('app/sounds/ringtones'),
            storage_path('app/public/sounds'),
            storage_path('app/public/sounds/ringtones'),
            public_path('sounds'),
            public_path('sounds/ringtones'),
        ];

        $foundFiles = [];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $matches = glob($dir . '/*.{mp3,wav,ogg,m4a,MP3,WAV,OGG,M4A}', GLOB_BRACE);
            if ($matches) {
                foreach ($matches as $filePath) {
                    $filename = basename($filePath);
                    $foundFiles[$filename] = $filePath;
                }
            }
        }

        ksort($foundFiles);

        $ringtones = [];

        foreach ($foundFiles as $filename => $filePath) {
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

            $label = match (strtolower($nameWithoutExt)) {
                'ringtone' => 'Ringtone 1 (Default)',
                'ringtone1' => 'Ringtone 1',
                'ringtone2' => 'Ringtone 2',
                'ringtone3' => 'Ringtone 3',
                default => ucfirst(str_replace(['_', '-'], ' ', $nameWithoutExt)),
            };

            $ringtones[] = [
                'value' => $filename,
                'label' => $label,
            ];
        }

        if (empty($ringtones)) {
            $ringtones[] = [
                'value' => 'ringtone.mp3',
                'label' => 'Ringtone 1 (Default)',
            ];
        }

        return array_values($ringtones);
    }
}
