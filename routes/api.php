<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Payments\PaymentsController;
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

Route::get('/test', function () {
    // app()->setLocale('uk');
    // return __('test');
    // dd(route('monobank.webhook'));
    return now();
});

Route::prefix('payments')->group(function(){
    Route::post('/monobank/webhook', [PaymentsController::class, 'monobankHandler'])->name('monobank.webhook');
    Route::get('/monobank/key', [PaymentsController::class, 'loadPublicKey'])->name('monobank.key');
});
