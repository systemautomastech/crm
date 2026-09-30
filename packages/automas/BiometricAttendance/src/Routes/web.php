<?php

use Illuminate\Support\Facades\Route;
use Automas\BiometricAttendance\Http\Controllers\BiometricSettingController;
use Automas\BiometricAttendance\Http\Controllers\BiometricAttendanceController;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:BiometricAttendance'])->group(function () {
    Route::prefix('biometric-attendance')->name('biometric-attendance.')->group(function () {

        // Setting Routes
        Route::get('/settings', [BiometricSettingController::class, 'index'])->name('settings');            
        Route::post('/setting-save', [BiometricSettingController::class, 'update'])->name('settings.save');
        
        // Attendance Routes
        Route::get('/', [BiometricAttendanceController::class, 'index'])->name('index');
        Route::get('/{employeeCode}/{date}', [BiometricAttendanceController::class, 'show'])->name('show');
        Route::post('/sync', [BiometricAttendanceController::class, 'sync'])->name('sync');
        Route::post('/sync-all-by-date-range', [BiometricAttendanceController::class, 'syncAllByDateRange'])->name('sync-all-by-date-range');
        
    });
});