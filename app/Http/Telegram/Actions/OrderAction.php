<?php

namespace App\Http\Telegram\Actions;

use App\Http\Services\Paginator\PaginatorService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Preorder;
use App\Models\UserChat;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

trait OrderAction
{
    private array $orderStatusIcon = [
        Order::STATUS_WAITING => '⌛',
        Order::STATUS_IN_ROAD => '🛵',
        Order::STATUS_COMPLETED => '🏁',
    ];

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
                ->action('inputAddress')
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

    public function inputAddress()
    {
        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');
        $pizzaSizeId = $this->data->get('sizeId');

        $preorder = new Preorder();
        $preorder->pizza = [
            'sizeId' => $pizzaSizeId,
            'pizzaId' => $pizzaId,
        ];

        $preorder->save();

        $this->messageInputAddress($messageId, $preorder->id);
    }

    private function messageInputAddress(string $messageId, string $preorderId)
    {
        $translation = [
            'inputAddress' => __('main.input_address'),
        ];

        $this->chat->last_message_id = $messageId;
        $this->chat->action = UserChat::ACTION_INPUT_ADDRESS;
        $this->chat->action_data = json_encode([
            'preorderId' => $preorderId,
        ]);
        $this->chat->save();

        $this->chat
            ->editCaption($messageId)
            ->message($translation['inputAddress'])
            ->send();
    }

    public function choosePayment()
    {
        $translation = [
            'backText' => __('main.actions.return_back', [], $this->chat->locale),
            'total'    => __('main.total'),
            'payment'  => __('main.choose_payment_method'),
        ];

        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');

        $preorder = Preorder::find($preorderId);
        $pizzaId = $preorder->pizza['pizzaId'];

        $pizza = Pizza::find($pizzaId);
        $size = PizzaSize::find($preorder->pizza['sizeId']);

        $message = $pizza->name . PHP_EOL . PHP_EOL;
        $message .= $translation['total'] . ': ' . $pizza->base_price * $size->price_multiplier . '$';
        $message .= "\n\n";
        $message .= $translation['payment'];

        $message .= '';


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
            'toMain'       => __('main.actions.to_main', [], $this->chat->locale),
            'cancel'         => __('main.actions.cancel'),
            'payNow'         => __('main.actions.pay_now'),
            'changePayments' => __('main.actions.change_type_of_payments'),
            'total'          => __('main.total'),
            'payment'        => __('main.payment_type'),
            'update'         => __('main.actions.update'),
            'address'        => __('main.address'),
        ];

        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');
        $paymentMethod = $this->data->get('paymentMethod');

        $preorder = Preorder::find($preorderId);
        if (!isset($preorder->pizza['address'])) {
            $this->messageInputAddress($messageId, $preorderId);
            return;
        }
        $pizzaData = $preorder->pizza;

        $pizzaId = $pizzaData['pizzaId'];
        $pizzaSizeId = $pizzaData['sizeId'];

        $pizza = Pizza::find($pizzaId);
        $size = PizzaSize::find($pizzaSizeId);

        $total = $pizza->base_price * $size->price_multiplier;
        $message = $pizza->name . PHP_EOL . PHP_EOL;
        $message .= $translation['total'] . ': ' . $total . '$';
        $message .= "\n\n";
        $message .= $translation['payment'] . ": monobank\n";
        $message .= $translation['address'] . ": " . $preorder->pizza['address'];

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
        $order->address = $preorder->pizza['address'];
        $order->total = $total;
        $order->user_id = $this->chat->user_id;
        $order->save();

        $notification = new Notification();
        $notification->user_id = $order->user_id;
        $notification->message = __('main.notifications.wait_payment', ['order' => $order->orderId]);
        $notification->type = Notification::TYPE_WAIT_PAYMENT;
        $notification->save();

        OrderPizza::create([
            'order_id' => $order->id,
            'pizza_id' => $pizza->id,
            'pizza_size_id' => $size->id,
            'count' => 1,
        ]);

        $buttons = [
            Button::make($translation['payNow'])->url($monobankResponse->pageUrl),
            Button::make($translation['cancel'])->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            Button::make($translation['update'])->action('orderView')->param('messageId', $messageId)->param('orderId', $order->id),
            Button::make($translation['toMain'])->param('messageId', $messageId)->action('toPreview'),
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
                Button::make($translation['yes'])->action('cancelOrderConfirmed')->param('messageId', $messageId)->param('orderId', $orderId),
                Button::make($translation['no'])->action('orderView')->param('messageId', $messageId)->param('orderId', $orderId),
            ])->chunk(2);

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
            'address'        => __('main.address'),
        ];

        $message = $translation['orderTitle'] . PHP_EOL . PHP_EOL;
        $message .= $translation['orderPrice'] . $order->total . '$' . PHP_EOL . PHP_EOL;
        $message .= $translation['status'] . ': ' . $translation['orderStatus'] . PHP_EOL;
        $message .= $translation['address'] . ': ' . $order->address . PHP_EOL;

        $message .= $translation['payment'] . ': ';

        if ($order->payment_type == Order::MONOBANK_TYPE)
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

        $buttons = [];
        if (!$order->paid_at && $order->invoice_link) {
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

    public function activeOrders()
    {
        $limit = 6;
        $page = $this->data->get('page') ?? 1;
        $messageId = $this->data->get('messageId');
        $userId = $this->chat->user_id;

        $translation = [
            'update'   => __('main.actions.update'),
            'backText' => __('main.actions.to_main'),
            'next'     => __('main.actions.next_page'),
            'prev'     => __('main.actions.prev_pgae'),
            'caption'  => __('main.manage_orders_text'),
        ];

        $orders = Order::where('user_id', $userId)
            ->orderByRaw("FIELD(status, '" . Order::STATUS_WAITING . "', '" . Order::STATUS_IN_ROAD . "', '" . Order::STATUS_COMPLETED . "')")
            ->get()
            ->groupBy('status');

        $waitingOrders = $orders->get(Order::STATUS_WAITING, collect());
        $inRoadOrders = $orders->get(Order::STATUS_IN_ROAD, collect());
        $completedOrders = $orders->get(Order::STATUS_COMPLETED, collect());

        $allOrders = $waitingOrders->concat($inRoadOrders)->concat($completedOrders);

        $paginatorService = new PaginatorService($page, $limit);
        $paginator = $paginatorService->paginateCollection($allOrders);

        $paginationButtons = [];
        if ($page != 1)
            $paginationButtons[] = Button::make($translation['prev'])->action('activeOrders')->param('messageId', $messageId)->param('page', $page - 1);

        if ($paginator->hasMorePages()) {
            $paginationButtons[] = Button::make($translation['next'])->action('activeOrders')->param('messageId', $messageId)->param('page', $page + 1);
        }

        $buttons = [];

        foreach ($paginator->items() as $order) {
            $label = '#' . $order->order_id . ' ' . $this->orderStatusIcon[$order->status];
            $buttons[] = Button::make($label)->action("orderView")->param('messageId', $messageId)->param('orderId', $order->id);
        }

        $caption = $translation['caption'] . PHP_EOL . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_WAITING) . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_IN_ROAD) . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_COMPLETED) . PHP_EOL;

        $keyboard = Keyboard::make()
            ->buttons($buttons)->chunk(2);

        count($paginationButtons) && $keyboard->row($paginationButtons);
        
        $keyboard->buttons([
            Button::make($translation['update'])->action("activeOrders")->param('messageId', $messageId),
            Button::make($translation['backText'])->action("toPreview")->param('messageId', $messageId),
        ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($caption)
            ->send();
    }

    private function confirmOrderAddress(string $address, array $actionData)
    {
        $translation = [
            'yes'     => __('main.actions.yes'),
            'no'      => __('main.actions.no'),
            'caption' => __('main.is_that_your_address', ['addrees' => $address]),
        ];

        $preorderId = $actionData['preorderId'];
        $this->chat->action = null;
        $this->chat->action_data = null;

        if (!$preorderId) {
            return;
        }
        $preorder = Preorder::findOrFail($preorderId);
        $preorderData = $preorder->pizza;
        $preorderData['address'] = $address;
        $preorder->pizza = $preorderData;
        $preorder->save();

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['no'])->action('reinputOrderAddress'),
            Button::make($translation['yes'])->action('choosePayment'),
        ])->chunk(2);

        $message =  $this->chat
            ->photo($this->introImage)
            ->message($translation['caption'])
            ->keyboard($keyboard)
            ->send();


        $messageId = $message->telegraphMessageId();
        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['no'])->action('reinputOrderAddress')->param('messageId', $messageId)->param('preorderId', $preorderId),
            Button::make($translation['yes'])->action('choosePayment')->param('messageId', $messageId)->param('preorderId', $preorderId),
        ])->chunk(2);
        $this->chat->replaceKeyboard($messageId, $keyboard)->send();
    }

    public function reinputOrderAddress()
    {
        $messageId = $this->data->get('messageId');
        $preorderId = $this->data->get('preorderId');

        $this->messageInputAddress($messageId, $preorderId);
    }
}
