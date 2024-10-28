<?php

namespace App\Http\Telegram\Actions\Settings;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class SettingsAction
{
    private TelegraphChat $chat;

    private array $availableLanguage = [
        'uk' => 'Українська 🇺🇦',
        'en' => 'English 🇬🇧',
    ];

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
        app()->setLocale($this->chat->locale);
    }

    public function showSettings(string $messageId)
    {
        $translation = [
            'toMain'   => __('main.actions.to_main'),
            'language' => __('main.actions.language'),
            'caption'  => __('main.settings.main_caption'),
        ];

        $keyboard = Keyboard::make()
            ->buttons([
                Button::make($translation['language'])->action('onSettingLanguage')->param('messageId', $messageId),
                Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
            ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($translation['caption'])
            ->send();
    }

    public function onSettingLanguage(string $messageId)
    {
        $translation = [
            'toMain'   => __('main.actions.to_main'),
            'language' => __('main.actions.language'),
            'caption'  => __('main.settings.choose_language'),
            'back'     => __('main.actions.return_back'),
        ];

        $languageButtons = [];

        foreach ($this->availableLanguage as $locale => $label) {
            $languageButtons[] = Button::make($label)->action('onChangeLanguage')->param('locale', $locale)->param('messageId', $messageId);
        }

        $keyboard = Keyboard::make()
            ->buttons($languageButtons)->chunk(3)
            ->buttons([
                Button::make($translation['back'])->action('showSettings')->param('messageId', $messageId),
                Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
            ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($translation['caption'])
            ->send();
    }

    public function onChangeLanguage(string $messageId, string $locale)
    {
        if (array_key_exists($locale, $this->availableLanguage)) {
            $this->chat->locale = $locale;
            $this->chat->save();
            app()->setLocale($locale);
        }

        $this->showSettings($messageId);
    }
}
