<?php

namespace App\Telegram;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler
{
    public function hello(string $name): void
    {
        $this->reply("Hi, $name!");
    }

    public function help(): void
    {
        $this->reply('Hi! again...');
    }

    public function actions(): void
    {
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
    }

    public function like(): void
    {
        Telegraph::message('Ty!')->send();
    }

    public function subscribe(): void
    {
        $this->reply("Ty for {$this->data->get('channel_name')}");
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        if ($text->value() === '/start') {
            $this->reply('Hello world :-)');
        } else {
            $this->reply(false);
        }
    }
}