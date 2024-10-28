<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Notification\NotificationAction;

trait NotificationTrait
{
    private NotificationAction $notificationAction;
    private int $limit = 10;

    public function indexNotification(): void
    {
        $page = $this->data->get('page') ?? 1;
        $messageId = $this->data->get('messageId');

        $this->notificationAction->setChat($this->chat);
        $this->notificationAction->indexNotification($messageId, $page);
    }

    public function showNotification(): void
    {
        $messageId = $this->data->get('messageId');
        $notificationId = $this->data->get('notificationId');

        $this->notificationAction->setChat($this->chat);
        $this->notificationAction->showNotification($messageId, $notificationId);
    }
}
