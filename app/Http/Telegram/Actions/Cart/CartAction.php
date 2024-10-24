<?php

namespace App\Http\Telegram\Actions\Cart;

use App\Http\Services\Paginator\PaginatorService;
use App\Http\Telegram\Actions\AbstractAction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderPizza;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Preorder;
use App\Models\UserChat;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class CartAction extends AbstractAction
{
    private TelegraphChat $chat;

    private int $maxPizzaCount = 15;
    private int $countPerRow = 5;

    public function __construct() {}

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
    }

    public function indexPizza(string $messageId)
    {
        $translation = [
            'message'  => __('main.choose_pizza', [], $this->chat->locale),
            'backText' => __('main.actions.return_back', [], $this->chat->locale),
        ];

        $pizzaButtons = Pizza::get()
            ->map(function ($pizza) use ($messageId) {
                return Button::make($pizza->name . ' ' . $pizza->base_price . '$')
                    ->action("onChoosePizza")
                    ->param('messageId', $messageId)
                    ->param('pizzaId', $pizza->id);
            });

        $pizzaButtons[] = Button::make($translation['backText'])
            ->action("toPreview")
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($pizzaButtons);


        $chat = $this->chat->replaceKeyboard($messageId, $keyboard);

        $this->replaceIntroImage($chat, $messageId, $translation['message']);
    }

    public function onChoosePizza(string $messageId, string $pizzaId)
    {
        $backText = __('main.actions.return_back', [], $this->chat->locale);

        $mapImage = [
            1 => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxleHBsb3JlLWZlZWR8MXx8fGVufDB8fHx8fA%3D%3D',
            2 => 'https://cdn.pixabay.com/photo/2024/04/18/10/41/ai-generated-8704060_640.jpg',
            3 => 'https://media.istockphoto.com/id/1442417585/photo/person-getting-a-piece-of-cheesy-pepperoni-pizza.jpg?s=612x612&w=0&k=20&c=k60TjxKIOIxJpd4F4yLMVjsniB4W1BpEV4Mi_nb4uJU=',
            4 => 'https://cdn.pixabay.com/photo/2014/07/08/12/34/pizza-386717_640.jpg',
            5 => 'https://s2-oglobo.glbimg.com/0vdubgDan1JOW67cMl2krKV-a_s=/0x0:3568x3568/888x0/smart/filters:strip_icc()/i.s3.glbimg.com/v1/AUTH_da025474c0c44edd99332dddb09cabe8/internal_photos/bs/2023/H/L/kRho18SPyam1rwl0z5hQ/side-view-pizza-with-chopped-pepper-board-cookware.jpg',
        ];

        $pizza = Pizza::findOrFail($pizzaId);
        $pizzaPreview = asset($mapImage[$pizza->id]);
        // $pizzaPreview=asset($pizza->image);

        $sizeButtons = $pizza->sizes->map(function ($size) use ($messageId, $pizza) {
            return Button::make($size->name . ' ' . $size->diametr_cm . ' ')
                ->action('onChoosePizzaSize')
                ->param('messageId', $messageId)
                ->param('pizzaId', $pizza->id)
                ->param('sizeId', $size->id);
        });

        $sizeButtons[] = Button::make($backText)
            ->action("indexPizza")
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($sizeButtons);

        $chat = $this->chat
            ->replaceKeyboard($messageId, $keyboard);

        $this->customEditPhoto($chat, $messageId, $pizza->name, $pizzaPreview);
    }

    public function onChoosePizzaSize(string $messageId, string $pizzaId, string $sizeId)
    {
        $translation = [
            'selectCount' => __('main.select_count'),
            'update'      => __('main.actions.update'),
            'back'        => __('main.actions.return_back'),
            'toMain'      => __('main.actions.to_main'),
        ];

        $pizzaData = [
            'sizeId' => $sizeId,
            'pizzaId' => $pizzaId,
        ];

        $preorder = Preorder::where('user_id', $this->chat->user_id)->first();

        if ($preorder) {
            $pizzas = $preorder->pizzas ?? [];
            $pizzas[] = $pizzaData;
            $preorder->pizzas = $pizzas;
        } else {
            $preorder = new Preorder();
            $preorder->pizzas = $pizzaData;
            $preorder->user_id = $this->chat->user_id;
        }
        $preorder->save();

        $buttons = [];
        for ($index = 0; $index < $this->maxPizzaCount; $index++) {
            $buttons[] = Button::make($index + 1)
                ->action('onSelectCount')
                ->param('messageId', $messageId)
                ->param('pizzaId', $pizzaId)
                ->param('sizeId', $sizeId)
                ->param('count', $index + 1);
        }


        $keyboard = Keyboard::make()
            ->buttons($buttons)
            ->chunk($this->countPerRow)
            ->buttons([
                Button::make($translation['update'])->action('onChoosePizzaSize')->param('messageId', $messageId)->param('pizzaId', $pizzaId)->param('sizeId', $sizeId),
                Button::make($translation['back'])->action('onChoosePizza')->param('messageId', $messageId)->param('pizzaId', $pizzaId),
                Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId)->param('pizzaId', $pizzaId),
            ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($translation['selectCount'])
            ->send();

        // $this->messageInputAddress($messageId, $preorder->id);
    }

    public function onSelectCount(string $messageId, string $pizzaId, string $sizeId, int $count)
    {
        $preorder = Preorder::where('user_id', $this->chat->user_id)->firstOrFail();

        $preorderPizza = [
            'pizzaId' => $pizzaId,
            'sizeId' => $sizeId,
            'count' => $count,
        ];

        $flagIsEdited = false;
        $cartPizzas = $preorder->pizzas ?? [];
        foreach ($cartPizzas as &$row) {
            if (empty($row['pizzaId']) || empty($row['sizeId']))
                continue;
            if ($row['pizzaId'] != $preorderPizza['pizzaId'] || $row['sizeId'] != $preorderPizza['sizeId']) {
                continue;
            }
            $flagIsEdited = true;
            Log::info('edited');
            $row['count'] = isset($row['count'])
                ? $row['count'] + $preorderPizza['count']
                : $preorderPizza['count'];
        }

        if (!$flagIsEdited) {
            $cartPizzas[] = $preorderPizza;
        }


        $preorder->pizzas = $cartPizzas;
        $preorder->save();

        $this->showCartConformation($messageId, $preorder->id);
    }

    public function indexCartPayments(string $messageId, string $preorderId)
    {
        $translation = [
            'backText' => __('main.actions.return_back', [], $this->chat->locale),
            'total'    => __('main.total'),
            'payment'  => __('main.choose_payment_method'),
        ];

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
            ->action("indexCartPayments")
            ->param('preorderId', $preorder->id)
            ->param('messageId', $messageId)
            ->param('paymentMethod', 'mono');

        $payments[] = Button::make($translation['backText'])
            ->action("onChoosePizzaSize")
            ->param('pizzaId', $pizzaId)
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($payments);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)->message($message)
            ->send();
    }

    public function messageInputAddress(string $messageId, string $preorderId)
    {
        $translation = [
            'inputAddress' => __('main.input_address'),
            'toMain'       => __('main.actions.to_main'),
            'back'         => __('main.actions.back'),
        ];

        $this->chat->last_message_id = $messageId;
        $this->chat->action = UserChat::ACTION_INPUT_ADDRESS;
        $this->chat->action_data = json_encode([
            'preorderId' => $preorderId,
        ]);
        $this->chat->save();

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['back'])->action('showCartConformation')->param('messageId', $messageId)->param('preorderId', $preorderId),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId)->param('preorderId', $preorderId),
        ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($translation['inputAddress'])
            ->send();
    }

    public function onConfirmCartAddress(string $address, array $actionData)
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
            Button::make($translation['no'])->action('reinputCartAddress'),
            Button::make($translation['yes'])->action('choosePayment'),
        ])->chunk(2);

        $message =  $this->chat
            ->photo($this->introImage)
            ->message($translation['caption'])
            ->keyboard($keyboard)
            ->send();


        $messageId = $message->telegraphMessageId();
        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['no'])->action('reinputCartAddress')->param('messageId', $messageId)->param('preorderId', $preorderId),
            Button::make($translation['yes'])->action('choosePayment')->param('messageId', $messageId)->param('preorderId', $preorderId),
        ])->chunk(2);
        $this->chat->replaceKeyboard($messageId, $keyboard)->send();
    }

    public function onSizeChoosed($messageId, $preorderId) {}

    public function showCartConformation(string $messageId, string $preorderId = '')
    {
        $translation = [
            'toMain'     => __('main.actions.to_main'),
            'clearCart'  => __('main.actions.clear_cart'),
            'update'     => __('main.actions.update'),
            'itemCount'  => __('main.item_count'),
            'store'      => __('main.order_complicity'),
            'morePizza'  => __('main.actions.more_pizza'),
            'toMain'     => __('main.actions.to_main'),
            'emptyCart'  => __('main.empty_cart'),
        ];

        if ($preorderId) {
            $preorder = Preorder::find($preorderId);
        } else {
            $preorder = Preorder::where('user_id', $this->chat->user_id)->first();
        }

        if (!$preorder) {
            $keyboard = Keyboard::make()->buttons([
                Button::make($translation['update'])->action('showCartConformation')->param('messageId', $messageId)->param('preorderId', $preorderId),
                Button::make($translation['morePizza'])->action('indexPizza')->param('messageId', $messageId),
                Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
            ]);

            $this->chat
                ->replaceKeyboard($messageId, $keyboard)
                ->editCaption($messageId)
                ->message($translation['emptyCart'])
                ->send();
            return;
        }
        $cartPizzas = $preorder->pizzas;

        $pizzaMap = [];
        foreach ($cartPizzas as $row) {
            $newCount = $row['count'] ?? 1;

            $count = isset($pizzaMap[$row['pizzaId']]['count'])
                ? $pizzaMap[$row['pizzaId']]['count'] + $newCount
                : $newCount;

            $pizzaMap[$row['pizzaId']] = [
                'sizeId' => $row['sizeId'],
                'pizzaId' => $row['pizzaId'],
                'count' =>   $count,
            ];
        }

        $message = $translation['store'] . PHP_EOL . PHP_EOL;

        $pizzaModelMap = Pizza::with('sizes')->find(array_keys($pizzaMap))->groupBy('id');

        foreach ($pizzaMap as $pizzaRow) {
            $pizzaId = $pizzaRow['pizzaId'];
            $sizeId  = $pizzaRow['sizeId'];
            $count   = $pizzaRow['count'];

            $pizza = $pizzaModelMap[$pizzaId]->first();
            $size = $pizza->sizes->where('id', $sizeId)->first();

            $pricePerItem = round($pizza->base_price * $size->price_multiplier, 2);
            $message .= $pizza->name . ' ' . $pricePerItem . '$' . PHP_EOL;
            $message .= $count . $translation['itemCount'] . ' - ' . round($pricePerItem * $count, 2) . '$ ';
            $message .= PHP_EOL . PHP_EOL;
        };

        $keyboard = Keyboard::make()->buttons([
            Button::make($translation['update'])->action('showCartConformation')->param('messageId', $messageId)->param('preorderId', $preorderId),
            Button::make($translation['morePizza'])->action('indexPizza')->param('messageId', $messageId),
            Button::make($translation['clearCart'])->action('onClearCart')->param('messageId', $messageId)->param('preorderId', $preorderId),
            Button::make($translation['toMain'])->action('toPreview')->param('messageId', $messageId),
        ]);

        $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editCaption($messageId)
            ->message($message)
            ->send();
    }

    public function onClearCart($messageId)
    {
        Preorder::where('user_id', $this->chat->user_id)->delete();
        $this->showCartConformation($messageId);
    }
}
