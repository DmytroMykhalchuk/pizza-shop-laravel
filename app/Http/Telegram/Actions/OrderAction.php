<?php

namespace App\Http\Telegram\Actions;

use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Preorder;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

trait OrderAction
{
    public function orderPizza()
    {
        $translation = [
            'message'  => __('main.choose_pizza', [], $this->chat->locale),
            'backText' => __('main.actions.return_back', [], $this->chat->locale),
        ];

        $messageId = $this->data->get('messageId');

        $pizzaButtons = Pizza::get()
            ->map(function ($pizza) use ($messageId) {
                return Button::make($pizza->name . ' ' . $pizza->base_price . '$')
                    ->action("choosePizza")
                    ->param('messageId', $messageId)
                    ->param('pizzaId', $pizza->id);
            });

        $pizzaButtons[] = Button::make($translation['backText'])
            ->action("toPreview")
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($pizzaButtons);


        $this->modifiedChat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($messageId, $translation['message']);
    }

    public function choosePizza()
    {
        $backText = __('main.actions.return_back', [], $this->chat->locale);

        $mapImage = [
            1 => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxleHBsb3JlLWZlZWR8MXx8fGVufDB8fHx8fA%3D%3D',
            2 => 'https://cdn.pixabay.com/photo/2024/04/18/10/41/ai-generated-8704060_640.jpg',
            3 => 'https://media.istockphoto.com/id/1442417585/photo/person-getting-a-piece-of-cheesy-pepperoni-pizza.jpg?s=612x612&w=0&k=20&c=k60TjxKIOIxJpd4F4yLMVjsniB4W1BpEV4Mi_nb4uJU=',
            4 => 'https://cdn.pixabay.com/photo/2014/07/08/12/34/pizza-386717_640.jpg',
            5 => 'https://s2-oglobo.glbimg.com/0vdubgDan1JOW67cMl2krKV-a_s=/0x0:3568x3568/888x0/smart/filters:strip_icc()/i.s3.glbimg.com/v1/AUTH_da025474c0c44edd99332dddb09cabe8/internal_photos/bs/2023/H/L/kRho18SPyam1rwl0z5hQ/side-view-pizza-with-chopped-pepper-board-cookware.jpg',
        ];

        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');

        $pizza = Pizza::findOrFail($pizzaId);
        $pizzaPreview = asset($mapImage[$pizza->id]);
        // $pizzaPreview=asset($pizza->image);

        $sizeButtons = $pizza->sizes->map(function ($size) use ($messageId, $pizza) {
            return Button::make($size->name . ' ' . $size->diametr_cm . ' ' . $pizza->base_price * $size->price_multiplier . '$')
                ->action('choosePizzaSize')
                ->param('messageId', $messageId)
                ->param('pizzaId', $pizza->id)
                ->param('sizeId', $size->id);
        });

        $sizeButtons[] = Button::make($backText)
            ->action("orderPizza")
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($sizeButtons);

        $this->modifiedChat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->customEditPhoto($messageId, $pizza->name, $pizzaPreview);
    }

    public function choosePizzaSize()
    {
        $translation = [
            'backText' => __('main.actions.return_back', [], $this->chat->locale),
            'total'    => __('main.total'),
            'payment'  => __('main.choose_payment_method'),
        ];

        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');
        $pizzaSizeId = $this->data->get('sizeId');

        $pizza = Pizza::find($pizzaId);
        $size = PizzaSize::find($pizzaSizeId);

        $message = $pizza->name . PHP_EOL . PHP_EOL;
        $message .= $translation['total'] . ': ' . $pizza->base_price * $size->price_multiplier . '$';
        $message .= "\n\n";
        $message .= $translation['payment'];

        $message .= '';

        $preorder = new Preorder();
        $preorder->pizza = [
            'sizeId' => $pizzaSizeId,
            'pizzaId' => $pizzaId,
        ];
        $preorder->save();
        $payments = [];

        $payments[] = Button::make('Monobank')
            ->action("orderPay")
            ->param('preorderId', $preorder->id)
            ->param('messageId', $messageId)
            ->param('paymentMethod', 'mono');

        $payments[] = Button::make($translation['backText'])
            ->action("choosePizza")
            ->param('pizzaId', $pizzaId)
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($payments);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function orderPay()
    {
        $translation = [
            'backText'       => __('main.actions.return_back', [], $this->chat->locale),
            'cancel'         => __('main.actions.cancel'),
            'payNow'         => __('main.actions.pay_now'),
            'changePayments' => __('main.actions.change_type_of_payments'),
            'total'          => __('main.total'),
            'payment'        => __('main.payment_type'),
            'update'         => __('main.actions.update'),
        ];

        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');
        $paymentMethod = $this->data->get('paymentMethod');

        $preorder = Preorder::find($preorderId);
        $pizzaData = $preorder->pizza;

        $pizzaId = $pizzaData['pizzaId'];
        $pizzaSizeId = $pizzaData['sizeId'];

        $pizza = Pizza::find($pizzaId);
        $size = PizzaSize::find($pizzaSizeId);

        $total = $pizza->base_price * $size->price_multiplier;
        $message = $pizza->name . PHP_EOL . PHP_EOL;
        $message .= $translation['total'] . ': ' . $total . '$';
        $message .= "\n\n";
        $message .= $translation['payment'] . " : monobank\n";

        $message .= '';

        $monobankResponse = $this->monobankService->createInvoice($total);
        $order = new Order();
        $order->delivery_type = Order::COURIER_TYPE;
        $order->payment_type = Order::MONOBANK_TYPE;
        $order->status = Order::STATUS_WAITING;
        $order->paid_at = null;
        $order->invoice_link = $monobankResponse->pageUrl;
        $order->invoice_id = $monobankResponse->invoiceId;
        $order->telegraph_chat_id = $this->chat->id;
        $order->message_id = $messageId;
        $order->total = $total;
        $order->save();

        OrderPizza::create([
            'order_id' => $order->id,
            'pizza_id' => $pizza->id,
            'pizza_size_id' => $size->id,
            'count' => 1,
        ]);

        $buttons = [
            Button::make($translation['payNow'])->url($monobankResponse->pageUrl),
            Button::make($translation['cancel'])->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            // Button::make($translation['changePayments'])->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make($translation['update'])->action('orderView')->param('messageId', $messageId)->param('orderId', $order->id),
            Button::make($translation['backText'])->param('messageId', $messageId)->action('toPreview'),
        ];

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function cancelOrder()
    {
        $translation = [
            'canceled_imposible' => __('main.canceled_imposible'),
            'canceled_posible' => __('main.canceled_posible'),
            'canceled_posible_recharge' => __('main.canceled_posible_recharge'),
            'yes' => __('main.actions.yes'),
            'no' => __('main.actions.no'),
            'ok' => __('main.actions.ok'),
        ];
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $order = Order::findOrFail($orderId);

        $message = $order->status !== Order::STATUS_WAITING
            ? $translation['canceled_posible'] . ($order->paid_at === Order::MONOBANK_TYPE ? $translation['canceled_posible_recharge'] : '')
            : $translation['canceled_posible'];

        $keyboard = $order->status !== Order::STATUS_WAITING
            ? Keyboard::make()->buttons([
                Button::make($translation['ok'])->action('toPreview')->param('messageId', $messageId),
            ])
            : Keyboard::make()->buttons([
                Button::make($translation['no'])->action('orderView')->param('messageId', $messageId)->param('orderId', $orderId),
                Button::make($translation['yes'])->action('cancelOrderConfirmed')->param('messageId', $messageId)->param('orderId', $orderId),
            ]);

        $this->modifiedChat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($messageId, $message);
    }

    public function changePaymentType()
    {
        $preorderId = $this->data->get('preorderId');
    }

    public function orderView()
    {
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $order = Order::with(['pizzas' => function ($query) {
            $query->with('size')->with('pizza');
            return $query;
        }])->findOrFail($orderId);

        $translation = [
            'backText'       => __('main.actions.return_back', [], $this->chat->locale),
            'cancel'         => __('main.actions.cancel'),
            'payNow'         => __('main.actions.pay_now'),
            'changePayments' => __('main.actions.change_type_of_payments'),
            'total'          => __('main.total'),
            'toMain'         => __('main.actions.to_main'),
            'payment'        => __('main.payment_type'),
            'update'         => __('main.actions.update'),
            'orderTitle'     => __('main.order_title_id', ['number' => $order->order_id]),
            'status'         => __('main.status.item'),
            'orderStatus'    => __('main.status.' . $order->status),
            'complicity'     => __('main.order_complicity'),
            'itemCount'      => __('main.item_count'),
            'statusPaid'     => __('main.paid.paid'),
            'statusNotPaid'  => __('main.paid.waiting'),
            'payment'        => __('main.paid.item'),
            'orderPrice'     => __('main.order_price'),
        ];

        $message = $translation['orderTitle'] . PHP_EOL . PHP_EOL;
        $message .= $translation['orderPrice'] . $order->total . '$' . PHP_EOL . PHP_EOL;
        $message .= $translation['status'] . ': ' . $translation['orderStatus'] . PHP_EOL;
        $message .= $translation['payment'] . ': ';
        $message .= $order->paid_at
            ? $translation['statusPaid']
            : $translation['statusNotPaid'];

        $message .= PHP_EOL . PHP_EOL . $translation['complicity'] . ': ' . PHP_EOL;
        foreach ($order->pizzas as $orderPizza) {
            Log::info($orderPizza);
            $message .= '- ' . $orderPizza->pizza->name . ' ' . $orderPizza->size->name . ' ';
            $message .= round($orderPizza->size->price_multiplier * $orderPizza->pizza->base_price, 2) . '$ ';
            // $message .= $pizza->pivot->count . $translation['itemCount'];
            $message .= PHP_EOL;
        }

        Log::info($order);
        $buttons = [];
        if (!$order->paid_at) {
            $buttons[] = Button::make($translation['payNow'])->url($order->invoice_link);
        }

        $buttons = array_merge($buttons, [
            Button::make($translation['cancel'])->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            // Button::make($translation['changePayments'])->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make($translation['update'])->action('orderView')->param('messageId', $messageId)->param('orderId', $orderId),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
        ]);

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function cancelOrderConfirmed()
    {
        $translation = [
            'recharged' => __('main.canceled_posible_recharge'),
            'success' => __('main.success.order_cancled'),
            'error' => __('main.error.order_completed'),
            'toMain' => __('main.actions.to_main'),
            'back' => __('main.actions.return_back'),
        ];
        $orderId = $this->data->get('orderId');
        $messageId = $this->data->get('messageId');

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId)->param('orderId', $orderId),
        ]);


        $order = Order::findOrFail($orderId);

        if ($order->status === Order::STATUS_COMPLETED) {
            $message = $translation['error'];
            $keyboard[] = Button::make($translation['back'])->action('orderView')->param('messageId', $messageId);
        } else {
            $message = $translation['success'];
        }

        if ($order->paid_at) {
            $this->monobankService->invalidInvoiceId($order->invoice_id);
        } else {
            $this->monobankService->cancelInvoice($order->invoice_id);
            $message .= "\n" . $translation['recharged'];
        }

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();

        return;
    }
}
