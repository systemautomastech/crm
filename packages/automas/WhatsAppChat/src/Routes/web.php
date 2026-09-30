<?php

use Automas\WhatsAppChat\Http\Controllers\WhatsAppChatSettingsController;
use Automas\WhatsAppChat\Http\Controllers\WhatsAppChatController;
use Automas\WhatsAppChat\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:WhatsAppChat'])->group(function () {
    
    Route::get('/whatsapp-chat', [WhatsAppChatController::class, 'index'])->name('whatsapp-chat.index');    
    Route::post('/send-whatsappchat-message', [WhatsAppController::class, 'sendWhatsappChatMessage'])->name('send.whatsappchat.message');
    
    Route::post('/whatsapp-chat/get-chat', [WhatsAppChatController::class, 'getChat'])->name('whatsapp-chat.get-chat');
    Route::post('/whatsapp-chat/mark-seen', [WhatsAppChatController::class, 'markAsSeen'])->name('whatsapp-chat.mark-seen');
    Route::post('/whatsapp-chat/update-name', [WhatsAppChatController::class, 'updateName'])->name('whatsapp-chat.update-name');
    Route::post('/whatsapp-chat/send-message', [WhatsAppController::class, 'sendMessageToContact'])->name('whatsapp-chat.send-message');
        
    // Settings
    Route::post('/whatsapp-chat/settings', [WhatsAppChatSettingsController::class, 'update'])->name('whatsapp-chat.settings.update');
});

Route::any('/whatsapp/webhook/{slug}', [WhatsAppController::class, 'receiveMessage'])->middleware('web')->withoutMiddleware([VerifyCsrfToken::class])->name('whatsappchat.webhook');