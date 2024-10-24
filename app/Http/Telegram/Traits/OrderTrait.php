<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Order\OrderAction;

trait OrderTrait
{
    private OrderAction $orderAction;

    public function onConfirmOrder()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');
        $paymentMethod = $this->data->get('paymentMethod');

        $this->orderAction->setChat($this->chat);
        $this->orderAction->onConfirmOrder($messageId, $preorderId, $paymentMethod);
    }

    public function onCancelOrder()
    {
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $this->orderAction->setChat($this->chat);
        $this->orderAction->onCancelOrder($messageId, $orderId);
    }

    // public function changePaymentType()
    // {
    //     $preorderId = $this->data->get('preorderId');
    // }

    public function onViewOrder()
    {
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $this->orderAction->setChat($this->chat);
        $this->orderAction->onCancelOrder($messageId, $orderId);
    }

    public function onConfirmCancelOrder()
    {
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $this->orderAction->setChat($this->chat);
        $this->orderAction->onConfirmCancelOrder($messageId, $orderId);
    }

    public function indexActiveOrders()
    {
        $page = $this->data->get('page') ?? 1;
        $messageId = $this->data->get('messageId');
        $userId = $this->chat->user_id;

        $this->orderAction->setChat($this->chat);
        $this->orderAction->onCancelOrder($messageId, $page, $userId);
    }
}
