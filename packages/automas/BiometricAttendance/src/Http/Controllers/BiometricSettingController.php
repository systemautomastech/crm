<?php

namespace Automas\BiometricAttendance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Automas\BiometricAttendance\Models\BiometricSetting;

class BiometricSettingController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-biometric-settings')) {
            $setting = BiometricSetting::where('created_by', creatorId())->first();
        
            return Inertia::render('BiometricAttendance/Settings', [
                'setting' => $setting
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
        
    }

    public function update(Request $request)
    {
        if (Auth::user()->can('edit-biometric-settings')) {
            $request->validate([
                'zkteco_api_url' => 'nullable|url',
                'username' => 'nullable|string|max:255',
                'password' => 'nullable|string|max:255',
                'is_zkteco_sync' => 'boolean',
            ]);

            $setting = BiometricSetting::updateOrCreate(
                ['created_by' => creatorId()],
                array_merge(
                    $request->only(['zkteco_api_url', 'username', 'password', 'is_zkteco_sync']),
                    ['created_by' => creatorId()]
                )
            );

            // Generate auth token if API URL and credentials are provided
            if ($request->zkteco_api_url && $request->username && $request->password) {
                $authToken = $this->generateAuthToken($request);
                if ($authToken) {
                    $setting->update(['auth_token' => $authToken]);
                }
            }

            return redirect()->route('biometric-attendance.settings')->with('success', 'Settings saved successfully');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    private function generateAuthToken(Request $request)
    {
        $url = $request->zkteco_api_url . '/api-token-auth/';
        $headers = [
            "Content-Type: application/json"
        ];
        $data = [
            "username" => $request->username,
            "password" => $request->password
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $authResponse = json_decode($response, true);
            return $authResponse['token'] ?? null;
        }

        return null;
    }
}
