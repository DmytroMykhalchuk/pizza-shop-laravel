<?php

namespace App\Http\Telegram\Actions;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Pizza;
use App\Models\Preorder;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

abstract class AbstractAction
{
    public $introImage = 'https://preview.redd.it/mystery-shack-gift-shop-as-a-video-game-pixel-art-v0-2s6kfkdcu25a1.png?width=1080&crop=smart&auto=webp&s=c863b652b2d8f2b3f2dd816b2c069fd8ba6a2b3f';

    protected function replaceIntroImage($chat, string $messageId, $caption)
    {
        $this->customEditPhoto($chat, $messageId, $caption, $this->introImage);
    }

    protected function toPreview(TelegraphChat $chat, string $messageId)
    {
        $title = __('main.intro_title', [], $chat->locale);
        $text = __('main.intro_description', [], $chat->locale);
        $caption = $title . "\n\n\n" . $text;

        $keyboard = $this->getPreviewKeyboard($chat, $messageId);

        $chat = $chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($chat, $messageId, $caption);
    }

    protected function getPreviewKeyboard($chat, string $messageId)
    {
        $userId = $chat->user_id;
        $ordersCount = Order::where('user_id', $userId)->count();
        $notificationCount = Notification::where('user_id', $userId)->where('is_checked', false)->count();

        $translation = [
            'orderPizza'    => __('main.actions.order_pizza', [], $chat->locale),
            'notifications' => __('main.actions.notifications', [], $chat->locale),
            'activeOrders'  => __('main.actions.active_orders', [], $chat->locale) . $ordersCount,
            'update'        => __('main.actions.update'),
        ];

        $caption = $translation['notifications'];
        if ($notificationCount) {
            $caption .= ' +' . $notificationCount;
        }

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['orderPizza'])->action("orderPizza")->param('messageId', $messageId),
            Button::make($caption)->action("indexNotification")->param('messageId', $messageId),
            Button::make($translation['activeOrders'])->action("activeOrders")->param('messageId', $messageId),
            Button::make($translation['update'])->action("toPreview")->param('messageId', $messageId),
            Button::make('Seed +20 orders 💻')->action("seed")->param('messageId', $messageId),
        ]);

        return $keyboard;
    }

    protected function customEditPhoto($chat, string $messageId, string $caption, string $image)
    {
        Log::alert('f');
        $response = $chat
            ->editMedia($messageId)
            ->photo($image)
            ->withData('media', json_encode([
                'type' => 'photo',
                'media' => $image,
                'caption' => $caption,
            ]))
            ->send();
    }

    protected function deleteMessage(TelegraphChat $chat, string $messageId)
    {
        $response = $chat->deleteMessage($messageId)->send();
        if (!$response->telegraphOk()) {
            $caption = __('main.reset_message_text');

            $chat = $chat->editCaption($messageId)->message($caption);
            $this->customEditPhoto($chat, $messageId, $caption, $this->introImage);
        }
    }

    protected function calculateOrderData(Preorder $preorder)
    {
        $translation = [
            'store'     => __('main.order_complicity'),
            'itemCount' => __('main.item_count'),
        ];

        $total = 0;

        $pizzaIds = array_map(function ($pizzaRow) {
            return $pizzaRow['pizzaId'] ?? 0;
        }, $preorder->pizzas);

        $pizzas = Pizza::with('sizes')->find($pizzaIds);
        $pizzaModelMap = $pizzas->groupBy('id');

        $message = $translation['store'] . PHP_EOL . PHP_EOL;

        foreach ($preorder->pizzas as $pizzaRow) {
            $pizzaId = $pizzaRow['pizzaId'];
            $sizeId  = $pizzaRow['sizeId'];
            $count   = $pizzaRow['count'];

            $pizza = $pizzaModelMap[$pizzaId]->first();
            $size = $pizza->sizes->where('id', $sizeId)->first();

            $pricePerItem = round($pizza->base_price * $size->price_multiplier, 2);
            $total += round($pricePerItem * $count, 2);

            $pricePerItem = round($pizza->base_price * $size->price_multiplier, 2);
            $message .= $pizza->name . ' ' . $pricePerItem . '$' . PHP_EOL;
            $message .= $count . $translation['itemCount'] . ' - ' . round($pricePerItem * $count, 2) . '$ ';
            $message .= PHP_EOL . PHP_EOL;
        };

        return [
            'total'    => $total,
            'pizzaMap' => $pizzas,
            'message'  => $message,
        ];
    }
}
