<?php

namespace App\Http\Telegram\Actions;

use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

trait SeedersAction
{

    public function seed()
    {
        $countPizzasNoPayment = 14;
        $countPizzasWithPayment = 3;
        $withPayment = true;

        for ($index = 0; $index < $countPizzasNoPayment; $index++) {
            $this->seedOrder();
        }

        for ($index = 0; $index < $countPizzasWithPayment; $index++) {
            $this->seedOrder($withPayment);
        }

        $this->toPreview();
    }

    private function seedOrder($withPayment = false)
    {
        $messageId = $this->data->get('messageId');

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
        $order->user_id = $this->chat->user_id;
        $order->save();

        OrderPizza::create([
            'order_id' => $order->id,
            'pizza_id' => $pizza->id,
            'pizza_size_id' => $size->id,
            'count' => 1,
        ]);
    }
}
