<?php

namespace App\Http\Telegram\Actions\Order;

use App\Http\Services\MonobankService\MonobankService;
use App\Http\Services\Paginator\PaginatorService;
use App\Http\Telegram\Actions\AbstractAction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Preorder;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class OrderAction extends AbstractAction
{
    private TelegraphChat $chat;
    private MonobankService $monobankService;

    private array $orderStatusIcon = [
        Order::STATUS_WAITING => '⌛',
        Order::STATUS_IN_ROAD => '🛵',
        Order::STATUS_COMPLETED => '🏁',
    ];

    public function __construct()
    {
        $this->monobankService = new MonobankService();
    }

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
        app()->setLocale($this->chat->locale);
    }

    public function onConfirmOrder(string $messageId, string $preorderId, string $paymentMethod)
    {
        $translation = [
            'toMain'         => __('main.actions.to_main'),
            'cancel'         => __('main.actions.cancel'),
            'payNow'         => __('main.actions.pay_now'),
            'changePayments' => __('main.actions.change_type_of_payments'),
            'total'          => __('main.total'),
            'payment'        => __('main.payment_type'),
            'update'         => __('main.actions.update'),
            'address'        => __('main.address'),
        ];


        $preorder = Preorder::find($preorderId);
        $orderData = $this->calculateOrderData($preorder);

        $total = $orderData['total'];

        $message = $orderData['message'];
        $message .= "\n\n";
        $message .= $translation['payment'] . ": monobank\n";
        $message .= $translation['address'] . ": " . $preorder->address;
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
        $order->address = $preorder->address;
        $order->total = $total;
        $order->user_id = $this->chat->user_id;
        $order->save();

        $notification = new Notification();
        $notification->user_id = $order->user_id;
        $notification->message = __('main.notifications.wait_payment', ['order' => $order->orderId]);
        $notification->type = Notification::TYPE_WAIT_PAYMENT;
        $notification->save();

        foreach ($preorder->pizzas as $pizzaRow) {
            OrderPizza::create([
                'order_id' => $order->id,
                'pizza_id' => $pizzaRow['pizzaId'],
                'pizza_size_id' => $pizzaRow['sizeId'],
                'count' => $pizzaRow['count'] ?? 1,
            ]);
        }
        $preorder->delete();

        $buttons = [
            Button::make($translation['payNow'])->url($monobankResponse->pageUrl),
            Button::make($translation['cancel'])->action('onCancelOrder')->param('orderId', $order->id)->param('messageId', $messageId),
            Button::make($translation['update'])->action('onViewOrder')->param('messageId', $messageId)->param('orderId', $order->id),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
        ];

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function onCancelOrder(string $messageId, string $orderId)
    {
        $translation = [
            'canceled_imposible' => __('main.canceled_imposible'),
            'canceled_posible' => __('main.canceled_posible'),
            'canceled_posible_recharge' => __('main.canceled_posible_recharge'),
            'yes' => __('main.actions.yes'),
            'no' => __('main.actions.no'),
            'ok' => __('main.actions.ok'),
        ];

        $order = Order::findOrFail($orderId);

        $message = $order->status === Order::STATUS_WAITING
            ? $translation['canceled_posible'] . ($order->paid_at === Order::MONOBANK_TYPE ? $translation['canceled_posible_recharge'] : '')
            : $translation['canceled_imposible'];

        $keyboard = $order->status === Order::STATUS_WAITING
            ? Keyboard::make()->buttons([
                Button::make($translation['yes'])->action('onConfirmCancelOrder')->param('messageId', $messageId)->param('orderId', $orderId),
                Button::make($translation['no'])->action('onViewOrder')->param('messageId', $messageId)->param('orderId', $orderId),
            ])->chunk(2)
            : Keyboard::make()->buttons([
                Button::make($translation['ok'])->action('toPreview')->param('messageId', $messageId),
            ]);

        $chat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($chat, $messageId, $message);
    }

    public function indexActiveOrders(string $messageId, string $page, string $userId)
    {
        $limit = 6;

        $translation = [
            'update'   => __('main.actions.update'),
            'backText' => __('main.actions.to_main'),
            'next'     => __('main.actions.next_page'),
            'prev'     => __('main.actions.prev_page'),
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
            $paginationButtons[] = Button::make($translation['prev'])->action('indexActiveOrders')->param('messageId', $messageId)->param('page', $page - 1);

        if ($paginator->hasMorePages()) {
            $paginationButtons[] = Button::make($translation['next'])->action('indexActiveOrders')->param('messageId', $messageId)->param('page', $page + 1);
        }

        $buttons = [];

        foreach ($paginator->items() as $order) {
            $label = '#' . $order->order_id . ' ' . $this->orderStatusIcon[$order->status];
            $buttons[] = Button::make($label)->action("onViewOrder")->param('messageId', $messageId)->param('orderId', $order->id);
        }

        $caption = $translation['caption'] . PHP_EOL . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_WAITING) . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_IN_ROAD) . PHP_EOL;
        $caption .= __('main.statuses.' . Order::STATUS_COMPLETED) . PHP_EOL;

        $keyboard = Keyboard::make()
            ->buttons($buttons)->chunk(2);

        count($paginationButtons) && $keyboard->row($paginationButtons);

        $keyboard->buttons([
            Button::make($translation['update'])->action("indexActiveOrders")->param('messageId', $messageId),
            Button::make($translation['backText'])->action("toPreview")->param('messageId', $messageId),
        ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($caption)
            ->send();
    }

    public function onConfirmCancelOrder(string $messageId, string $orderId)
    {
        $translation = [
            'recharged' => __('main.canceled_posible_recharge'),
            'success' => __('main.success.order_cancled'),
            'error' => __('main.error.order_completed'),
            'toMain' => __('main.actions.to_main'),
            'back' => __('main.actions.return_back'),
        ];

        $buttons = [
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId)->param('orderId', $orderId),
        ];

        $order = Order::findOrFail($orderId);

        if ($order->status === Order::STATUS_COMPLETED) {
            $message = $translation['error'];
            $buttons[] = Button::make($translation['back'])->action('onViewOrder')->param('messageId', $messageId);
        } else {
            $order->delete();
            $message = $translation['success'];
        }

        if ($order->paid_at) {
            $this->monobankService->invalidInvoiceId($order->invoice_id);
        } else {
            $this->monobankService->cancelInvoice($order->invoice_id);
            $message .= "\n" . $translation['recharged'];
        }

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function onViewOrder(string $messageId, string $orderId)
    {
        $order = Order::with(['pizzas' => function ($query) {
            $query->with('size')->with('pizza');
            return $query;
        }])->findOrFail($orderId);

        $translation = [
            'backText'       => __('main.actions.return_back'),
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


        if ($order->payment_type == Order::MONOBANK_TYPE) {
            $message .= $translation['payment'] . ': ';
            $message .= $order->paid_at
                ? $translation['statusPaid']
                : $translation['statusNotPaid'];
            $message .= PHP_EOL;
        }

        $message .= PHP_EOL . $translation['complicity'] . ': ' . PHP_EOL;
        foreach ($order->pizzas as $orderPizza) {
            $message .= '- ' . $orderPizza->pizza->name . ' ' . $orderPizza->size->name . ' ';
            $message .= round($orderPizza->size->price_multiplier * $orderPizza->pizza->base_price, 2) . '$ ' . $orderPizza->count . $translation['itemCount'];
            $message .= PHP_EOL;
        }

        $buttons = [];

        if (!$order->paid_at && $order->invoice_link) {
            $buttons[] = Button::make($translation['payNow'])->url($order->invoice_link);
        }

        if ($order->status === Order::STATUS_WAITING) {
            $buttons[] = Button::make($translation['cancel'])->action('onCancelOrder')->param('orderId', $order->id)->param('messageId', $messageId);
        }
        $buttons = array_merge($buttons, [
            // Button::make($translation['changePayments'])->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make($translation['update'])->action('onViewOrder')->param('messageId', $messageId)->param('orderId', $orderId),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
        ]);

        $keyboard = Keyboard::make()->buttons($buttons);

        $respons = $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();

        Log::info($respons);
    }
}
