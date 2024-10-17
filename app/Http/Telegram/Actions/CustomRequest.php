<?php

use Illuminate\Support\Facades\Http;

class CustomRequest
{
    public function editCustomMessage(
        string $messageId, 
        string $image, 
        string $caption = null, 
        array $keyboards = null, 
        string $chatId = '5153831236', 
        string $mediaType = 'photo'
    ) {
        // Використовуємо поточну дату, якщо підпис не задано
        $caption = $caption ?? now();
    
        // Підготовка reply_markup
        $replyMarkup = null;
        if ($keyboards) {
            $replyMarkup = [
                'inline_keyboard' => array_map(function ($row) {
                    return array_map(function ($button) {
                        return [
                            'text' => $button['text'],
                            'callback_data' => $button['callback_data']
                        ];
                    }, $row);
                }, $keyboards)
            ];
        }
    
        $response = Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/editMessageMedia", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => json_encode([
                'type' => $mediaType,
                'media' => $image,
                'caption' => $caption
            ]),
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
        ]);
    
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception("Failed to edit message: " . $response->body());
        }
    }
    
}
