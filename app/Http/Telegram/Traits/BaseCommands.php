<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\BaseCommands\BaseCommandsAction;
use App\Models\UserChat;
use DefStudio\Telegraph\DTO\InlineQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait BaseCommands
{
    private BaseCommandsAction $baseCommandAction;

    protected function handleUnknownCommand(Stringable $text): void
    {
        // if ($text->value() === '/start') {
        //     $this->reply('Hello world :-)');
        // } else {
        //     $this->reply('wtf?');
        // }
    }

    protected function onFailure(Throwable $throwable): void
    {
        if ($throwable instanceof NotFoundHttpException) {
            throw $throwable;
        }

        report($throwable);

        $this->reply('sorry man, I failed');
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $action = $this->chat->action;
        $actionData = json_decode($this->chat->action_data ?? '[]', true);

        $id = $this->message->id();
        $this->chat->deleteMessage($id)->send();

        $messageId = $this->chat->last_message_id;
        if (!$messageId) {
            Log::alert('no');
            return;
        }

        if ($action === UserChat::ACTION_INPUT_ADDRESS) {
            $this->onConfirmCartAddress($text, $actionData, $messageId);
        }
        // $this->chat->deleteMessage($id);
        // dd($id);
        // $this->chat->html("Received: $text " . $id)->send();
        // $this->chat->message('hi')->withData('caption', 'test')->send();
    }
}
