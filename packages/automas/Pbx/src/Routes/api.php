<?php

use Illuminate\Support\Facades\Route;
use Automas\Pbx\Http\Controllers\WebRtcConfigController;
use Automas\Pbx\Http\Controllers\ClickToCallController;
use Automas\Pbx\Http\Controllers\CallerLookupController;
use Automas\Pbx\Http\Controllers\PbxExtensionController;

Route::middleware([
    'web',
    'auth',
    'verified',
    'PlanModuleCheck:Pbx',
])->group(function () {
    Route::get('/pbx/webrtc-config', [WebRtcConfigController::class, 'index'])
        ->name('pbx.webrtc-config');

    Route::post('/pbx/click-to-call', [ClickToCallController::class, 'call'])
        ->name('pbx.click-to-call');

    Route::get('/pbx/caller-lookup', [CallerLookupController::class, 'lookup'])
        ->name('pbx.caller-lookup');
});

Route::middleware([
    'auth:sanctum',
    'verified',
    'PlanModuleCheck:Pbx',
])->prefix('app')->group(function () {
    Route::get('/pbx/webrtc-config', [WebRtcConfigController::class, 'index'])
        ->name('app.pbx.webrtc-config');

    Route::post('/pbx/click-to-call', [ClickToCallController::class, 'call'])
        ->name('app.pbx.click-to-call');

    Route::get('/pbx/caller-lookup', [CallerLookupController::class, 'lookup'])
        ->name('app.pbx.caller-lookup');

    Route::get('/pbx/extensions/directory', [PbxExtensionController::class, 'directory'])
        ->name('app.pbx.directory');

    Route::get('/pbx/extensions/live-status', [PbxExtensionController::class, 'liveStatus'])
        ->name('app.pbx.live-status');
});
