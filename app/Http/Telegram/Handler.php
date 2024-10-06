<?php

namespace App\Http\Telegram;

use App\Actions\Telegram\Objects\TelegramUserFrom;
use App\Actions\Telegram\TelegramUserAction;
use App\Http\Services\MonobankService\MonobankService;
use App\Models\Order;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Preorder;
use App\Models\UserChat;
use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\InlineQueryResult;
use DefStudio\Telegraph\DTO\InlineQueryResultArticle;
use DefStudio\Telegraph\DTO\InlineQueryResultPhoto;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends WebhookHandler
{
    private string $defaultLocale = 'en';
    private TelegramUserAction $userAction;
    private MonobankService $monobankService;

    public function __construct()
    {
        $this->userAction = new TelegramUserAction();
        $this->monobankService = new MonobankService();
    }

    public function start()
    {
        $image2 = 'https://preview.redd.it/mystery-shack-gift-shop-as-a-video-game-pixel-art-v0-2s6kfkdcu25a1.png?width=1080&crop=smart&auto=webp&s=c863b652b2d8f2b3f2dd816b2c069fd8ba6a2b3f';
        $image1 = asset('assets/main/pixel-shop.webp');

        $user = $this->message->from();
        $this->userAction->storeOrUpdate($this->chat->id, $user);

        $this->chat->locale = $user->languageCode() ?? $this->defaultLocale;
        $this->chat->save();

        $title = __('main.intro_title', [], $this->chat->locale);
        $text = __('main.intro_description', [], $this->chat->locale);
        $orderPizza = __('main.actions.order_pizza', [], $this->chat->locale);

        $response = $this->chat->photo($image2)->html($title . "\n\n\n" . $text)->send();
        Telegraph::setChatMenuButton()->webApp("Pizza 🍕", env('FRONTEND_URL'))->send();
        $messageId = $response->telegraphMessageId();

        $this->chat->replaceKeyboard($messageId, Keyboard::make()->buttons([
            Button::make($orderPizza)->action("orderPizza")->param('messageId', $messageId),
        ]))->send();
    }

    public function orderPizza()
    {
        $message = __('main.choose_pizza', [], $this->chat->locale);
        $backText = __('main.return_back', [], $this->chat->locale);

        $messageId = $this->data->get('messageId');

        $pizzaButtons = Pizza::get()->map(function ($pizza) use ($messageId) {
            return Button::make($pizza->name . ' ' . $pizza->base_price . '$')
                ->action("choosePizza")
                ->param('messageId', $messageId)
                ->param('pizzaId', $pizza->id);
        });

        $pizzaButtons[] = Button::make($backText)
            ->action("toPreview")
            ->param('messageId', $messageId);
        $keyboard = Keyboard::make()->buttons($pizzaButtons);

        $this->chat->editCaption($messageId)->message($message)->replaceKeyboard($messageId, $keyboard)
            ->send();
    }

    public function toPreview()
    {
        $messageId = $this->data->get('messageId');

        $title = __('main.intro_title', [], $this->chat->locale);
        $text = __('main.intro_description', [], $this->chat->locale);
        $orderPizza = __('main.actions.order_pizza', [], $this->chat->locale);

        $this->chat->editCaption($messageId)->html($title . "\n\n\n" . $text)->replaceKeyboard($messageId, Keyboard::make()->buttons([
            Button::make($orderPizza)->action("orderPizza")->param('messageId', $messageId),
        ]))->send();
    }

    public function choosePizza()
    {
        $backText = __('main.return_back', [], $this->chat->locale);

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
        // $pizzaPreview = asset($pizza->image);
        $pizzaPreview = asset($mapImage[$pizza->id]);

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

        $this->chat
            ->editCaption($messageId)->message($pizza->name)
            ->editMedia($messageId)->photo($pizzaPreview)
            ->editCaption($messageId)->message($pizza->description)
            ->replaceKeyboard($messageId, $keyboard)
            ->send();

        // $this->chat
    }

    public function choosePizzaSize()
    {
        $backText = __('main.return_back', [], $this->chat->locale);

        $messageId = $this->data->get('messageId');
        $pizzaId = $this->data->get('pizzaId');
        $pizzaSizeId = $this->data->get('sizeId');
        $pizza = Pizza::find($pizzaId);
        $size = PizzaSize::find($pizzaSizeId);

        $message = $pizza->name . PHP_EOL . PHP_EOL;
        $message .= 'Total: ' . $pizza->base_price * $size->price_multiplier . '$';
        $message .= "\n\n";
        $message .= "Choose payment method";

        $message .= '';
        Log::info(1);

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

        $payments[] = Button::make($backText)
            ->action("choosePizza")
            ->param('pizzaId', $pizzaId)
            ->param('messageId', $messageId);

        $keyboard = Keyboard::make()->buttons($payments);
        Log::info($message);

        $this->chat->editCaption($messageId)->message('hh')
            ->replaceKeyboard($messageId, $keyboard)
            ->send();
    }

    public function orderPay()
    {
        $backText = __('main.return_back', [], $this->chat->locale);

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
        $message .= 'Total: ' . $total . '$';
        $message .= "\n\n";
        $message .= "Payment method: monobank\n";

        $message .= '';

        $payments = [];

        // //order
        $monobankResponse = $this->monobankService->createInvoice($total);
        // Log::error($monobankResponse);
        $order = new Order();
        $order->delivery_type = Order::COURIER_TYPE;
        $order->payment_type = Order::ONLINE_TYPE;
        $order->paid_at = null;
        $order->invoice_link = $monobankResponse->pageUrl;
        $order->invoice_id = $monobankResponse->invoiceId;
        $order->telegraph_chat_id = $this->chat->id;
        $order->message_id = $messageId;
        $order->save();

        $message .= $monobankResponse->pageUrl;

        $buttons = [
            Button::make('Pay now!')->url($monobankResponse->pageUrl),
            Button::make('Cancel')->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            Button::make('Change type of payments')->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make('На головну')->param('messageId', $messageId)->action('toPreview'),
        ];

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat->replaceKeyboard($messageId, $keyboard)->editCaption($messageId)->message('TY!')

            ->send();
    }

    public function cancelOrder() {}

    public function k()
    {
        $order = Order::get()->last();
        $messageId = 822;
        $preorderId = Preorder::get()->last()->id;

        $message = 'Some mor ';

        $buttons = [
            Button::make('Pay now!')->url($order->invoice_link),
            Button::make('Cancel')->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            // Button::make('Change type of payments')->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make('На головну')->param('messageId', $messageId)->action('toPreview'),
        ];

        $keyboard = Keyboard::make()->buttons($buttons);

        $this->chat->replaceKeyboard($messageId, $keyboard)->editCaption($messageId)->html('ef')

            ->send();
    }

    public function st()
    {
        $order = Order::get()->last();
        $messageId = 822;
        $preorderId = Preorder::get()->last()->id;

        $message = 'Some mor ';

        $buttons = [
            Button::make('Pay now!')->url($order->invoice_link),
            Button::make('Cancel')->param('orderId', $order->id)->param('messageId', $messageId)->action('cancelOrder'),
            // Button::make('Change type of payments')->param('preorderId', $preorderId)->param('messageId', $messageId)->action('changePaymentType'),
            Button::make('На головну')->param('messageId', $messageId)->action('toPreview'),
        ];

        $keyboard = Keyboard::make()->buttons($buttons);



        $image2 = 'https://e7.pngegg.com/pngimages/748/778/png-clipart-ghost-pixel-art-gif-cute-pixel-angle-text-thumbnail.png';
        $image1 = 'https://preview.redd.it/mystery-shack-gift-shop-as-a-video-game-pixel-art-v0-2s6kfkdcu25a1.png?width=1080&crop=smart&auto=webp&s=c863b652b2d8f2b3f2dd816b2c069fd8ba6a2b3f';
        // $this->chat->editMedia(702)->photo($image2)->setTitle('f')->editCaption(702)->message(now())->send();
        // $this->chat->editCaption(702)->message('Price555!')->send();

        $this->chat->replaceKeyboard($messageId, $keyboard)->editMedia(822)
            ->photo($image2)
            ->editCaption(822)->message('ewg')
            ->send();
        return;
        // Потім редагуємо підпис для того ж повідомлення
        // $this->chat->editCaption(702)
        //     ->message(now())
        //     ->send();

        return;
        // $response = Telegraph::getRegisteredCommands()->send();
        Telegraph::registerBotCommands([
            'command1' => 'command 1 description',
            'command2' => 'command 2 description',
            'command3' => 'command 3 description',
        ])->send();

        // Telegraph::chatAction(ChatActions::TYPING)->send();
        // Telegraph::setTitle("my chat")->send();
        // Telegraph::setDescription("a test chat with my bot")->send();
        // Telegraph::setChatMenuButton()->default()->send(); //restore default 
        // Telegraph::setChatMenuButton()->commands()->send(); //show bot commands in menu button 
        Telegraph::setChatMenuButton()->webApp("Pizza 🍕", "https://dmytromykhalchuk.github.io/pizza-shop-react/")->send(); //show start web app button 
    }

    public function help(): void
    {
        Log::info("1");
        $this->reply('Hi! again...');
    }

    public function actions()
    {
        $this->reply('Hi! again...');

        Telegraph::message('Choose action')
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('visit site')->url('https://google.com'),
                    Button::make('like')->action('like'),
                    Button::make('subscribe')
                        ->action('subscribe')
                        ->param('channel_name', '5'),
                ])
            )->send();

        return http_response_code(200);
    }

    public function like(): void
    {
        Telegraph::message('Ty!')->send();
    }

    public function subscribe(): void
    {
        $this->reply("Ty for {$this->data->get('channel_name')}");
    }

    public function ttt()
    {
        $this->reply('1');
        $this->chat->setBaseUrl('https://docs.defstudio.it/telegraph/v1/api/bots')->message('secret message')->send();
    }

    public function test()
    {
        // $test=$this->reply('Test');
        // // Log::info($test);
        // // $this->edit(123456)->message("new text")->send();
        $chat = TelegraphChat::find(2);

        // this will use the default parsing method set in config/telegraph.php
        // $chat->message('hello')->send();

        // $chat->html("<b>hello</b>\n\nI'm a bot!")->send();

        $msg = $chat->markdown('*hello*')->send();

        // $chat->edit(143)->message("new g")->send();

        /**************************************************/
        // $response = $chat->message("new g")->send();
        // $message_id = $response->telegraphMessageId();
        // sleep(1);
        // $chat->edit($message_id)->message("new g" . now())->send();
        // sleep(1);
        // $chat->message('reply for')->reply($message_id)->send();
        /**************************************************/

        // $chat->message("ok!")->forceReply(placeholder: 'Honor...')->send();
        // $chat->message("please don't share this")->protected()->send();
        // $chat->message("late night message")->silent()->send();
        // $chat->message("http://my-blog.dev")->withoutPreview()->send();
        // Log::info($response->telegraphMessageId());

        // $response = $chat->message("new g")->send();
        // $message_id = $response->telegraphMessageId();
        // Log::info($message_id);
        // sleep(1);
        // $chat->deleteMessage($message_id);
        // $chat->edit(190)->message('edit ' . now())->send();
        // // sleep(1);
        // $chat->deleteMessage(190);

        /**************************************************/


        $message = $chat->message('hello world')
            ->keyboard(Keyboard::make()->buttons([
                Button::make("🗑️ Delete")->action("delete")->param('id', 190),
                Button::make("📖 Mark as Read")->action("read")->param('id', 190),
                Button::make("👀 Open")->url('https://test.it'),
                // Button::make("👀 Search")->,
            ])->chunk(2))->send();

        // $this->chat->
        Log::info($message);
    }

    public function dismiss(int $id)
    {
        $notificationId = $this->data->get('id');
        $this->reply('notificationId: ' . $notificationId);

        // Log::info($id);
        // Telegraph::deleteMessage($id);
        // Telegraph::message('Ty!')->send();
    }

    public function handleInlineQuery(InlineQuery $inlineQuery): void
    {
        $query = $inlineQuery->query(); // "pest logo"
        // if (is_int(!$query)) {
        //     return;
        // }
        $query = (int)$query;
        $queryNext = $query + 1;
        // Log::info($inlineQuery->id());

        Log::info(asset('assets/images/pepperoni.png'));

        $response =    $this->bot->answerInlineQuery($inlineQuery->id(), [
            InlineQueryResultArticle::make(random_int(2, 1000), 'Title', '/rows')->thumbUrl(asset('assets/images/pepperoni.png'))->description('fd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/pepperoni.png'), asset('assets/images/pepperoni.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/margarita-min.png'), asset('assets/images/margarita-min.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
        ])->send();

        Log::info($response);
        // $this->bot->

        // $this->bot->answerInlineQuery($inlineQuery->id(), [
        //     InlineQueryResultPhoto::make("Light", asset('assets/images/pepperoni.png'), asset('assets/images/pepperoni.png'))
        //         ->caption('Duck'),
        //     InlineQueryResultPhoto::make($queryNext . "-dark", "https://random-d.uk/api/v2/$queryNext.jpg", "https://random-d.uk/api/v2/$queryNext.jpg")
        //         ->caption('Duck' . $queryNext),
        // ])->send();
    }

    public function btn()
    {
        $this->chat->message('hello world')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Delete')->action('delete')->param('id', '42'),
                Button::make('open')->url('https://test.it'),
                Button::make('Web App')->webApp('https://web-app.test.it'),
                // Button::make('Login Url')->loginUrl('https://loginUrl.test.it'),

                Button::make('switch')->switchInlineQuery('')->currentChat(),
            ]))->send();
    }

    public function rows()
    {
        // $keyboard = Keyboard::make()
        //     ->row([
        //         Button::make('Delete')->action('delete')->param('id', '42'),
        //         Button::make('Dismiss')->action('dismiss')->param('id', '42'),
        //     ])
        //     ->row([
        //         Button::make('open')->url('https://test.it'),
        //     ]);

        // $keyboard = Keyboard::make()
        //     ->button('Delete')->action('delete')->param('id', '42')->width(0.5)
        //     ->button('Dismiss')->action('dismiss')->param('id', '42')->width(0.5)
        //     ->button('open')->url('https://test.it')
        //     ->button('open')->webApp('https://web-app.url.dev');
        //     // ->button('open')->loginUrl('https://login.url.dev');


        $keyboard = Keyboard::make()
            ->row([
                Button::make('Delete')->action('delete')->param('id', '42'),
                Button::make('Dismiss')->action('dismiss')->param('id', '42'),
            ])
            ->row([
                Button::make('open')->url('https://test.it'),
            ])
            ->rightToLeft();
        $this->chat->message('hello world')->keyboard($keyboard)->send();
    }

    public function delete()
    {
        $this->reply('44');
        Log::info('Message Id: ' . $this->actions());
    }

    public function rk()
    {

        // Telegraph::message('hello world')
        //     ->replyKeyboard(ReplyKeyboard::make()->buttons([
        //         ReplyButton::make('foo')->requestPoll(),
        //         ReplyButton::make('bar')->requestQuiz(),
        //         ReplyButton::make('baz')->webApp('https://webapp.dev'),
        //     ]))->send();

        // $keyboard = ReplyKeyboard::make()
        //     ->row([
        //         ReplyButton::make('Send Contact')->requestContact(),
        //         ReplyButton::make('Send Location')->requestLocation(),
        //     ])
        //     ->row([
        //         ReplyButton::make('Quiz')->requestQuiz(),
        //     ]);


        // $keyboard = ReplyKeyboard::make()
        //     ->button('Text')
        //     ->button('Send Contact')->requestContact()
        //     ->button('Send Location')->requestLocation()
        //     ->button('Create Quiz')->requestQuiz()
        //     ->button('Create Poll')->requestPoll()
        //     ->button('Start WebApp')->webApp('https://web.app.dev');

        // $keyboard = ReplyKeyboard::make()
        //     ->button('Send Contact')->requestContact()
        //     ->button('Send Location')->requestLocation()
        //     ->resize();

        // $keyboard = ReplyKeyboard::make()
        //     ->button('Text')
        //     ->button('Send Location')->requestLocation()
        //     ->oneTime();

        // $keyboard = ReplyKeyboard::make()
        // ->button('Text')
        // ->button('Send Location')->requestLocation()
        // ->selective();


        Telegraph::message('command received')
            ->removeReplyKeyboard()
            ->send();

        // Telegraph::message('hello world')
        //     ->replyKeyboard($keyboard)->send();
    }

    public function qu()
    {
        Telegraph::message('hello')->dispatch();
    }

    public function attach()
    {
        $pizza = Pizza::find(2);
        // $image = asset($pizza->image);
        $image = asset('assets/images/pepperoni.png');
        // Telegraph::message('hi')->withData('caption', 'test')->send();
        // // Telegraph::withData('caption', 'test')->message('hi')->send();
        // Telegraph::photo('https://e7.pngegg.com/pngimages/748/778/png-clipart-ghost-pixel-art-gif-cute-pixel-angle-text-thumbnail.png')->message('1')->send();
        // Telegraph::photo($image)->message('1')->send();
        // Log::info(asset('assets/images/pepperoni.png'));
        // Telegraph::location(12.345, -54.321)->send();
        // Telegraph::dice()->send();
        // Telegraph::dice(\DefStudio\Telegraph\Enums\Emojis::FOOTBALL)->send();

        //     Telegraph::document(Storage::path('brochure.pdf'))
        // ->thumbnail(Storage::path('brochure_thumbnail.jpg'))
        // ->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        if ($text->value() === '/start') {
            $this->reply('Hello world :-)');
        } else {
            $this->reply('wtf?');
        }
    }

    protected function onFailure(Throwable $throwable): void
    {
        if ($throwable instanceof NotFoundHttpException) {
            throw $throwable;
        }

        report($throwable);

        $this->reply('sorry man, I failed');
    }

    protected function handleChatMessage(Stringable $text): void
    {
        // in this example, a received message is sent back to the chat
        $this->chat->html("Received: $text")->send();
    }
}
