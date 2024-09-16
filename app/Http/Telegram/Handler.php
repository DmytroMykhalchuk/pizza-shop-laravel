<?php

namespace App\Http\Telegram;

use App\Models\Pizza;
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
    public function st()
    {
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
    public function hello(string $name): void
    {
        $this->reply("Hi, $name!");
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

    public function delete() {}

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
