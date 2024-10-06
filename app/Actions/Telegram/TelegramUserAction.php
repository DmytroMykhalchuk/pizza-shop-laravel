<?php

namespace App\Actions\Telegram;

use App\Models\UserChat;
use DefStudio\Telegraph\DTO\User;
use Illuminate\Support\Facades\Log;

class TelegramUserAction
{
    public function __construct() {}

    public function storeOrUpdate(string $chatId, User $user): void
    {
        if (!$chatId || empty($user->id())) {
            return;
        }
        $availableLocales = config('app.available_locale', []);
        $userLanguage = $user->languageCode();
        $chatLanguage = in_array($userLanguage, $availableLocales) ? $userLanguage : $availableLocales[0];

        UserChat::updateOrCreate([
            'telegraph_chat_id' => $chatId,
            'telegram_user_id' => $user->id(),
        ], [
            'first_name' => $user->firstName(),
            'last_name' => $user->lastName(),
            'username' => $user->username(),
            'language_code' => $chatLanguage,
        ]);
    }
}
