<?php

use Illuminate\Support\Facades\Route;
use Automas\AIBusinessAdvisor\Http\Controllers\AIAdvisorController;
use Automas\AIBusinessAdvisor\Http\Controllers\SuperAdminSettingsController;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:AIBusinessAdvisor'])->prefix('ai-advisor')->group(function () {
    Route::get('/dashboard', [AIAdvisorController::class, 'index'])->name('ai-advisor.dashboard');

    Route::post('/recommendations/{id}/mark-done', [AIAdvisorController::class, 'markDone'])->name('ai-advisor.recommendations.done');
    Route::post('/recommendations/{id}/dismiss', [AIAdvisorController::class, 'dismiss'])->name('ai-advisor.recommendations.dismiss');
    Route::post('/insights/{id}/dismiss', [AIAdvisorController::class, 'dismissInsight'])->name('ai-advisor.insights.dismiss');
    Route::post('/alerts/{id}/resolve', [AIAdvisorController::class, 'resolveAlert'])->name('ai-advisor.alerts.resolve');
    Route::post('/generate', [AIAdvisorController::class, 'generateNow'])->name('ai-advisor.generate');
});

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::post('/ai-advisor/settings/store', [SuperAdminSettingsController::class, 'store'])->name('ai-advisor.setting.store');
});
