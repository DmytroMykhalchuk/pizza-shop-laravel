<?php

namespace App\Http\Telegram;

use App\Actions\Telegram\TelegramUserAction;
use App\Http\Services\MonobankService\MonobankService;
use App\Http\Telegram\Actions\BaseHandlers;
use App\Http\Telegram\Actions\OrderAction;
use App\Http\Telegram\Actions\ScreenAction;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Telegraph as TelegraphTelegraph;
use Illuminate\Support\Facades\Log;

class Handler extends WebhookHandler
{
    use BaseHandlers;
    use OrderAction;
    use ScreenAction;

    private string $defaultLocale = 'en';
    private TelegramUserAction $userAction;
    private MonobankService $monobankService;
    private TelegraphTelegraph $modifiedChat;

    public function __construct()
    {
        $this->userAction = new TelegramUserAction();
        $this->monobankService = new MonobankService();
    }

    public function k()
    {
        $messageId = 1110;
        $image = 1;
        $image = "https://random-d.uk/api/v2/" . random_int(1, 280) . ".jpg";

        // Редагування медіа без зміни тексту
        // $this->chat
        // ->editMedia(1000)
        // // ->message('2')->photo($image)
        // ->mediaGroup([
        //     [
        //         'type' => 'photo',
        //         'media' => $image,
        //     ],
        // ])->editCaption(1000)->message('skkdfg')
        // ->editCaption(1000)->markdown("![Car Image]($image kk) kkk")
        // // ->editMedia(1000) // message_id
        // // ->photo($image) // Вказуємо нове фото
        // ->send(); // Надсилаємо запит
        // $this->chat->message('sdg')->editCaption($messageId)->message('dG')
        // ->editMedia($messageId)
        // ->photo($image)
        // ->editCaption($messageId)->message('sdg')
        // // ->($messageId)->markdown(now())
        // ->send();

        // $response = $this->chat
        //     ->editMedia($messageId)
        //     ->photo($image)
        //     ->editCaption($messageId)->message('erg')
        //     ->send();
        $keyboard = Keyboard::make()->buttons([
            Button::make('f4e')->action('rr'),
            Button::make('fffe')->action('rr'),
        ]);

        $response = $this->chat
            ->replaceKeyboard($messageId, $keyboard)
            ->editMedia($messageId)
            ->photo($image)
            ->withData('media', json_encode([
                'type' => 'photo',
                'media' => $image,
                'caption' => now()
            ]))
            ->send();

        // $response = $this->chat
        // ->editMedia($messageId)
        // ->photo($image)
        // ->editCaption($messageId)->message(now())
        // ->send();

        Log::info($response);
    }
}

// public function rk()
// {
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

    // $this->chat->message('fd')->replyKeyboard($keyboard)->send();
// }


// public function btn()
// {
//     $this->chat->message('hello world')
//         ->keyboard(Keyboard::make()->buttons([
//             Button::make('Delete')->action('delete')->param('id', '42'),
//             Button::make('open')->url('https://test.it'),
//             Button::make('Web App')->webApp('https://web-app.test.it'),
//             Button::make('switch')->switchInlineQuery('dd')->currentChat(),
//         ]))->send();
// }