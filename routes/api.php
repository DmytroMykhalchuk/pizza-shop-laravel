<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\TelegramWebhook\TelegramWebhookController;
use App\Http\Controllers\TelegramWebhook\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/set-webhook', [TelegramWebhookController::class, 'setWebhook']);

Route::get('/wd', function () {
    dd(Cache::get('wd'));
});

// Route::post('/webhook', [TelegramWebhookController::class, 'index']);
// Route::post('/webhook', WebhookController::class);

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
});
