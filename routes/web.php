<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/send-message', function () {
    $text = (string)view('telegram.test');
    $response = Http::post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
        'chat_id' => env('TELEGRAM_TARGET_USER'),
        'text' => $text,
        'parse_mode' => 'html',
        'reply_markup' => [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Button 1',
                        'callback_data' => '1',
                    ],
                    [
                        'text' => 'Button 2',
                        'callback_data' => '1',
                    ]
                ]
            ],
            'keyboard' => [
                [
                    [
                        'text' => 'Button 1',
                        'callback_data' => '1',
                    ],
                    [
                        'text' => 'Button 2',
                        'callback_data' => '1',
                    ]
                ]
            ],
            'resize_keyboard' => true,
        ],
    ]);

    return $response->json();
});
