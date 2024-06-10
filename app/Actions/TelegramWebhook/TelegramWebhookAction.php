<?php

namespace App\Actions\TelegramWebhook;

use Illuminate\Support\Facades\Http;

class TelegramWebhookAction
{
    public function setWebhook(string $url)
    {
        $response = Http::post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/setWebhook', [
            'url' => $url
        ]);

        dd($response->json());
    }
}
