<?php

namespace App\Http\Telegram\Actions\BaseCommands;

use App\Http\Telegram\Actions\AbstractAction;
use DefStudio\Telegraph\Models\TelegraphChat;

class BaseCommandsAction extends AbstractAction
{
    private TelegraphChat $chat;

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
        app()->setLocale($this->chat->locale);
    }

}
