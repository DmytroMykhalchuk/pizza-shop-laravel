<?php

namespace App\Http\Telegram;

use App\Http\Telegram\Actions\BaseCommands\BaseCommandsAction;
use App\Http\Telegram\Actions\Cart\CartAction;
use App\Http\Telegram\Actions\Notification\NotificationAction;
use App\Http\Telegram\Actions\Screen\ScreenAction;
use App\Http\Telegram\Actions\Order\OrderAction;
use App\Http\Telegram\Actions\Seeder\SeederAction;
use App\Http\Telegram\Actions\Settings\SettingsAction;
use App\Http\Telegram\Traits\BaseCommands;
use App\Http\Telegram\Traits\CartTrait;
use App\Http\Telegram\Traits\NotificationTrait;
use App\Http\Telegram\Traits\OrderTrait;
use App\Http\Telegram\Traits\ScreenTrait;
use App\Http\Telegram\Traits\SeederTrait;
use App\Http\Telegram\Traits\SettingsTrait;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

class Handler extends WebhookHandler
{
    use BaseCommands;

    use NotificationTrait;
    use SettingsTrait;
    use OrderTrait;
    use SeederTrait;
    use CartTrait;
    use ScreenTrait;

    private OrderAction $orderAction;
    private CartAction $cartAction;
    private SeederAction $seederAction;
    private SettingsAction $settingsAction;
    private NotificationAction $notificationAction;
    private ScreenAction $screenAction;
    private BaseCommandsAction $baseCommandAction;

    public function __construct()
    {
        $this->orderAction = new OrderAction();
        $this->cartAction = new CartAction();
        $this->seederAction = new SeederAction();
        $this->settingsAction = new SettingsAction();
        $this->notificationAction = new NotificationAction();
        $this->screenAction = new ScreenAction();
        $this->baseCommandAction = new BaseCommandsAction();
    }

    public function k()
    {
        Log::info($this->chat);
        // $this->reply(
        //     $this->chat
        // );
        // $messageId = 1110;
        // $image = 1;
        // $image = "https://random-d.uk/api/v2/" . random_int(1, 280) . ".jpg";

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
        // $keyboard = Keyboard::make()->buttons([
        //     Button::make('f4e')->action('rr'),
        //     Button::make('fffe')->action('rr'),
        // ]);

        // $response = $this->chat
        //     ->replaceKeyboard($messageId, $keyboard)
        //     ->editMedia($messageId)
        //     ->photo($image)
        //     ->withData('media', json_encode([
        //         'type' => 'photo',
        //         'media' => $image,
        //         'caption' => now()
        //     ]))
        //     ->send();

        // $response = $this->chat
        // ->editMedia($messageId)
        // ->photo($image)
        // ->editCaption($messageId)->message(now())
        // ->send();

        // Log::info($response);
    }
    public function btn()
    {
        $this->chat->message('1')
            ->keyboard(Keyboard::make()->buttons([
                Button::make(env('FRONTEND_URL'))->webApp(env('FRONTEND_URL')),
                // Button::make('Web App')->webApp('https://web-app.test.it'),
            ]))->send();
    }

    public function web()
    {
        $messageId = $this->message->id();
        $this->chat->deleteMessage($messageId)->send();

        Telegraph::setChatMenuButton()->webApp("Pizza 🍕", env('FRONTEND_URL'))->send();
    }
}

//     $this->chat->message('hello world')
//         ->keyboard(Keyboard::make()->buttons([
//             Button::make('Delete')->action('delete')->param('id', '42'),
//             Button::make('open')->url('https://test.it'),
//             Button::make('Web App')->webApp('https://web-app.test.it'),
//             Button::make('switch')->switchInlineQuery('dd')->currentChat(),
//         ]))->send();
// }