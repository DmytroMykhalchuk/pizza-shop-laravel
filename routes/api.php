<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Payments\PaymentsController;
use App\Http\Controllers\Pizza\PizzaController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
});

Route::prefix('pizza')->group(function () {
    Route::get('/search', [PizzaController::class, 'search']);
});


Route::prefix('payments')->group(function () {
    Route::post('/monobank/webhook', [PaymentsController::class, 'monobankHandler'])->name('monobank.webhook');
    Route::get('/monobank/key', [PaymentsController::class, 'loadPublicKey'])->name('monobank.key');
});
