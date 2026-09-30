<?php

use Automas\FacebookChat\Http\Controllers\FacebookChatSettingsController;
use Automas\FacebookChat\Http\Controllers\FacebookChatController;
use Automas\FacebookChat\Http\Controllers\FacebookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:FacebookChat'])->group(function () {
    Route::get('/facebook-chat', [FacebookChatController::class, 'index'])->name('facebook-chat.index');
    Route::post('/facebook-chat/get-chat', [FacebookChatController::class, 'getChat'])->name('facebook-chat.get-chat');
    Route::post('/facebook-chat/mark-seen', [FacebookChatController::class, 'markAsSeen'])->name('facebook-chat.mark-seen');
    Route::post('/facebook-chat/send-message', [FacebookController::class, 'sendFacebookMessageToContact'])->name('facebook-chat.send-message');
    // Settings
    Route::post('/facebook-chat/settings', [FacebookChatSettingsController::class, 'update'])->name('facebook-chat.settings.update');
});