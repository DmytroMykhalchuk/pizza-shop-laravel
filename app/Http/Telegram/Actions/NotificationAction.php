<?php

namespace App\Http\Telegram\Actions;

use App\Http\Services\Paginator\PaginatorService;
use App\Models\Notification;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

trait NotificationAction
{
    private int $limit = 10;

    public function indexNotification()
    {
        $page = $this->data->get('page') ?? 1;
        $messageId = $this->data->get('messageId');
        $userId = $this->chat->user_id;

        $translation = [
            'update'   => __('main.actions.update'),
            'backText' => __('main.actions.to_main'),
            'next'     => __('main.actions.next_page'),
            'prev'     => __('main.actions.prev_pgae'),
            'caption'  => __('main.notifications.items'),
        ];

        $notificationQuery = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        $paginatorService = new PaginatorService($page, $this->limit);
        $paginator = $paginatorService->paginate($notificationQuery);

        $paginationButtons = [];
        if ($page != 1)
            $paginationButtons[] = Button::make($translation['prev'])->action('indexNotification')->param('messageId', $messageId)->param('page', $page - 1);

        if ($paginator->hasMorePages()) {
            $paginationButtons[] = Button::make($translation['next'])->action('indexNotification')->param('messageId', $messageId)->param('page', $page + 1);
        }

        $this->readNotifications($paginator->items());

        $buttons = [];

        foreach ($paginator->items() as $notification) {
            $label = $notification->message;
            if (!$notification->is_checked) {
                $label .= ' ✨';
            }
            $buttons[] = Button::make($label)->action("showNotification")->param('messageId', $messageId)->param('notificationId', $notification->id);
        }

        $caption = $translation['caption'] . PHP_EOL . PHP_EOL;

        $keyboard = Keyboard::make()
            ->buttons($buttons);

        count($paginationButtons) &&
            $keyboard->row($paginationButtons);

        $keyboard->buttons([
            Button::make($translation['update'])->action("indexNotification")->param('messageId', $messageId),
            Button::make($translation['backText'])->action("toPreview")->param('messageId', $messageId),
        ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($caption)
            ->send();
    }

    public function showNotification()
    {
        $messageId = $this->data->get('messageId');
        $notificationId = $this->data->get('notificationId');

        $translation = [
            'update'  => __('main.actions.update'),
            'caption' => __('main.notifications.items'),
            'toMain'  => __('main.actions.to_main'),
            'back'    => __('main.actions.return_back'),
        ];

        $notification = Notification::findOrFail($notificationId);

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['update'])->action('showNotification')->param('messageId', $messageId)->param('notificationId', $notificationId),
            Button::make($translation['back'])->action('indexNotification')->param('messageId', $messageId),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
        ]);
        $message = $translation['caption'] . PHP_EOL . PHP_EOL;
        $message .= $notification->message . PHP_EOL . PHP_EOL;
        $message.=$notification->created_at->format('d-m-Y H:i');

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($message)
            ->send();
    }

    private function readNotifications(array $notifications)
    {
        $unreaded = array_filter($notifications, function ($notification) {
            return !$notification->is_checked;
        });

        $unreadedIds = array_map(function ($notification) {
            return $notification->id;
        }, $unreaded);

        if (count($unreadedIds)) {
            Notification::whereIn('id', $unreadedIds)->update(['is_checked' => true]);
        }
    }
}
