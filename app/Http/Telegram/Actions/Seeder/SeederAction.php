<?php

namespace App\Http\Telegram\Actions\Seeder;

use App\Http\Services\MonobankService\MonobankService;
use App\Http\Telegram\Actions\AbstractAction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use DefStudio\Telegraph\Models\TelegraphChat;

class SeederAction extends AbstractAction
{
    private TelegraphChat $chat;
    private MonobankService $monobankService;

    public function __construct()
    {
        $this->monobankService = new MonobankService();
    }

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
        app()->setLocale($this->chat->locale);
    }

    public function seed(string $messageId)
    {
        $countPizzasNoPayment = 14;
        $countPizzasWithPayment = 3;
        $withPayment = true;

        for ($index = 0; $index < $countPizzasNoPayment; $index++) {
            $this->seedOrder($messageId);
        }

        for ($index = 0; $index < $countPizzasWithPayment; $index++) {
            $this->seedOrder($messageId, $withPayment);
        }

        $this->toPreview($this->chat, $messageId);
    }

    private function seedOrder(string $messageId, $withPayment = false)
    {

        $pizza = Pizza::inRandomOrder()->first();
        $size = $pizza->sizes()->inRandomOrder()->first();

        $total = $pizza->base_price * $size->price_multiplier;

        $monobankResponse = $withPayment
            ? $this->monobankService->createInvoice($total)
            : (object)['pageUrl' => null, 'invoiceId' => null];

        $order = new Order();
        $order->delivery_type = Order::COURIER_TYPE;
        $order->payment_type = Order::OFFLINE_TYPE;
        $order->status = Order::STATUSES[random_int(0, count(Order::STATUSES) - 1)];
        $order->paid_at = null;
        $order->invoice_link = $monobankResponse->pageUrl;
        $order->invoice_id = $monobankResponse->invoiceId;
        $order->telegraph_chat_id = $this->chat->id;
        $order->message_id = $messageId;
        $order->total = $total;
        $order->address = 'Some address';
        $order->user_id = $this->chat->user_id;
        $order->save();

        OrderPizza::create([
            'order_id' => $order->id,
            'pizza_id' => $pizza->id,
            'pizza_size_id' => $size->id,
            'count' => 1,
        ]);

        $notification = new Notification();
        $notification->user_id = $order->user_id;
        if ($withPayment) {
            $notification->message = __('main.notifications.wait_payment', ['order' => $order->id]);
            $notification->type = Notification::TYPE_WAIT_PAYMENT;
        } else {
            $types = [Notification::TYPE_DELIVERED, Notification::TYPE_PAID];
            $targetType = $types[random_int(0, count($types) - 1)];

            $notification->message = __('main.notifications.' . $targetType, ['order' => $order->id]);
            $notification->type = $targetType;
        }
        $notification->save();
    }
}
