<?php

use Automas\Pbx\Http\Controllers\PbxCallLogController;
use Automas\Pbx\Http\Controllers\PbxDashboardController;
use Automas\Pbx\Http\Controllers\PbxExtensionController;
use Automas\Pbx\Http\Controllers\PbxSettingsController;
use Illuminate\Support\Facades\Route;
use Automas\Pbx\Http\Controllers\PbxCallReportController;

Route::middleware([
    'web',
    'auth',
    'verified',
    'PlanModuleCheck:Pbx',
])->prefix('pbx')->name('pbx.')->group(function () {
    Route::get('/dashboard', [PbxDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('settings', [PbxSettingsController::class, 'index'])
        ->name('settings.index');

    Route::post('settings', [PbxSettingsController::class, 'store'])
        ->name('settings.store');

    Route::get('extensions/directory', [PbxExtensionController::class, 'directory'])
        ->name('extensions.directory');

    Route::get('extensions/live-status', [PbxExtensionController::class, 'liveStatus'])
        ->name('extensions.live-status');

    Route::resource('extensions', PbxExtensionController::class)->names([
        'index' => 'extensions.index',
        'create' => 'extensions.create',
        'store' => 'extensions.store',
        'show' => 'extensions.show',
        'edit' => 'extensions.edit',
        'update' => 'extensions.update',
        'destroy' => 'extensions.destroy',
    ]);

    Route::get('call-logs', [PbxCallLogController::class, 'index'])
        ->name('call-logs.index');

    Route::post('store-call-events', [PbxCallLogController::class, 'storeEvent'])
        ->name('call-events.store');

    Route::get('/call-summary', [PbxCallReportController::class, 'summary'])
        ->name('call-reports.summary');

    Route::get('/call-reports', [PbxCallReportController::class, 'index'])
        ->name('call-reports.index');

    Route::get('/call-reports/recording', [PbxCallReportController::class, 'recording'])
        ->name('call-reports.recording');

    Route::get('ringtone', [PbxSettingsController::class, 'ringtone'])
        ->name('ringtone');
});
