<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Cart\CartAction;
use Illuminate\Support\Facades\Log;

trait CartTrait
{
    private CartAction $cartAction;

    public function indexPizza()
    {
        $messageId = $this->data->get('messageId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->indexPizza($messageId);
    }

    public function onChoosePizza()
    {
        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->onChoosePizza($messageId, $pizzaId);
    }

    public function onChoosePizzaSize()
    {
        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');
        $pizzaSizeId = $this->data->get('sizeId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->onChoosePizzaSize($messageId, $pizzaId, $pizzaSizeId);
    }

    public function indexCartPayments()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->indexCartPayments($messageId, $preorderId);
    }

    public function onConfirmCartAddress(string $address, array $actionData)
    {
        $this->cartAction->setChat($this->chat);
        $this->cartAction->onConfirmCartAddress($address, $actionData);
    }

    public function reinputCartAddress()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->messageInputAddress($messageId, $preorderId);
    }

    public function onSizeChoosed()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->messageInputAddress($messageId, $preorderId);
    }

    public function showCartConformation()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId')??'';

        $this->cartAction->setChat($this->chat);
        $this->cartAction->showCartConformation($messageId, $preorderId);
    }

    public function onSelectCount()
    {
        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');
        $sizeId = $this->data->get('sizeId');
        $count = $this->data->get('count');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->onSelectCount($messageId, $pizzaId, $sizeId, $count);
    }

    public function onClearCart(){
        $messageId = $this->data->get('messageId');

        $this->cartAction->setChat($this->chat);
        $this->cartAction->onClearCart($messageId);
    }
}
