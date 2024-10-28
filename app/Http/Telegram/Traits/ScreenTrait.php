<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Screen\ScreenAction;
use DefStudio\Telegraph\DTO\InlineQuery;

trait ScreenTrait
{
    private ScreenAction $screenAction;

    public function start(): void
    {
        $this->screenAction->setChat($this->chat);
        $this->screenAction->start($this->message);
    }

    public function handleInlineQuery(InlineQuery $inlineQuery): void
    {
        $this->screenAction->setChat($this->chat);
        $this->screenAction->handleInlineQuery($inlineQuery, $this->bot);
    }

    public function toPreview()
    {
        $messageId = $this->data->get('messageId');

        $this->screenAction->setChat($this->chat);
        $this->screenAction->showPreview($messageId);
    }

}
