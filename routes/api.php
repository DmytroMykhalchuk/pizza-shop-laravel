<?php

use App\Http\Controllers\TelegramWebhook\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// http://localhost:8001/api/set-webhook?url=https://17c9-46-211-158-19.ngrok-free.app/api/webhook
Route::get('/set-webhook', [TelegramWebhookController::class, 'setWebhook']);

Route::get('/webhook-data', function () {
    dd(Cache::get('wd'));
});

Route::post('/webhook', [TelegramWebhookController::class, 'index']);
