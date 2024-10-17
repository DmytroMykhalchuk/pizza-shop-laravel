<?php

namespace App\Http\Telegram\Actions;

use App\Models\Order;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

trait ScreenAction
{
    private $introImage = 'https://preview.redd.it/mystery-shack-gift-shop-as-a-video-game-pixel-art-v0-2s6kfkdcu25a1.png?width=1080&crop=smart&auto=webp&s=c863b652b2d8f2b3f2dd816b2c069fd8ba6a2b3f';

    public function replaceIntroImage(string $messageId, $caption)
    {
        $this->customEditPhoto($messageId, $caption, $this->introImage);
    }

    public function toPreview()
    {
        $title = __('main.intro_title', [], $this->chat->locale);
        $text = __('main.intro_description', [], $this->chat->locale);
        $caption = $title . "\n\n\n" . $text;
        $messageId = $this->data->get('messageId');

        $keyboard = $this->getPreviewKeyboard($messageId);

        $this->modifiedChat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($messageId, $caption);
    }

    private function getPreviewKeyboard($messageId)
    {
        $userId = $this->chat->user_id;
        $ordersCount = Order::where('user_id', $userId)->count();
        
        $translation = [
            'orderPizza'    => __('main.actions.order_pizza', [], $this->chat->locale),
            'notifications' => __('main.actions.notifications', [], $this->chat->locale),
            'activeOrders'  => __('main.actions.active_orders', [], $this->chat->locale) . $ordersCount,
            'update'        => __('main.actions.update'),
        ];

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['orderPizza'])->action("orderPizza")->param('messageId', $messageId),
            Button::make($translation['notifications'])->action("notifications")->param('messageId', $messageId),
            Button::make($translation['activeOrders'])->action("activeOrders")->param('messageId', $messageId),
            Button::make($translation['update'])->action("toPreview")->param('messageId', $messageId),
            Button::make('Seed +20 orders 💻')->action("seed")->param('messageId', $messageId),
        ]);

        return $keyboard;
    }

    public function customEditPhoto(string $messageId, string $caption, string $image)
    {
        $this->modifiedChat->editMedia($messageId)
            ->photo($image)
            ->withData('media', json_encode([
                'type' => 'photo',
                'media' => $image,
                'caption' => $caption,
            ]))->send();
    }
}
