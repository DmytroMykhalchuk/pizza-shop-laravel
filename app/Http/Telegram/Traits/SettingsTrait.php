<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Settings\SettingsAction;

trait SettingsTrait
{
    private SettingsAction $settingsAction;

    public function showSettings()
    {
        $messageId = $this->data->get('messageId');

        $this->settingsAction->setChat($this->chat);
        $this->settingsAction->showSettings($messageId);
    }

    public function onSettingLanguage()
    {
        $messageId = $this->data->get('messageId');

        $this->settingsAction->setChat($this->chat);
        $this->settingsAction->onSettingLanguage($messageId);
    }

    public function onChangeLanguage()
    {
        $messageId = $this->data->get('messageId');
        $locale = $this->data->get('locale');

        $this->settingsAction->setChat($this->chat);
        $this->settingsAction->onChangeLanguage($messageId, $locale);
    }
}
