<?php

namespace App\Http\Telegram\Actions;

use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\InlineQueryResultArticle;
use DefStudio\Telegraph\DTO\InlineQueryResultPhoto;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait BaseHandlers
{
    public function start()
    {
        $image1 = asset('assets/main/pixel-shop.webp');

        $translation = [
            'title' => __('main.intro_title', [], $this->chat->locale),
            'text' => __('main.intro_description', [], $this->chat->locale),
        ];

        $user = $this->message->from();
        $this->chat->locale = $user->languageCode() ?? $this->defaultLocale;
        $this->chat->user_id = $user->id();
        $this->chat->username = $user->id();
        $this->chat->first_name = $user->firstName();
        $this->chat->last_name = $user->lastName();
        $this->chat->username = $user->username();
        $this->chat->save();

        $response = $this->chat
            ->photo($this->introImage)
            ->html($translation['title'] . "\n\n\n" . $translation['text'])
            ->send();

        $messageId = $response->telegraphMessageId();
        $keyboard = $this->getPreviewKeyboard($messageId);
        $this->chat->replaceKeyboard($messageId, $keyboard)->send();

        Telegraph::setChatMenuButton()->webApp("Pizza 🍕", env('FRONTEND_URL'))->send();
    }


    public function handleInlineQuery(InlineQuery $inlineQuery): void
    {
        $query = $inlineQuery->query();
        $query = (int)$query;
        $queryNext = $query + 1;

        $response =    $this->bot->answerInlineQuery($inlineQuery->id(), [
            InlineQueryResultArticle::make(random_int(2, 1000), 'Title', '/rows')->thumbUrl(asset('assets/images/pepperoni.png'))->description('fd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/pepperoni.png'), asset('assets/images/pepperoni.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/margarita-min.png'), asset('assets/images/margarita-min.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
        ])->send();

        // $this->bot->answerInlineQuery($inlineQuery->id(), [
        //     InlineQueryResultPhoto::make("Light", asset('assets/images/pepperoni.png'), asset('assets/images/pepperoni.png'))
        //         ->caption('Duck'),
        //     InlineQueryResultPhoto::make($queryNext . "-dark", "https://random-d.uk/api/v2/$queryNext.jpg", "https://random-d.uk/api/v2/$queryNext.jpg")
        //         ->caption('Duck' . $queryNext),
        // ])->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        // if ($text->value() === '/start') {
        //     $this->reply('Hello world :-)');
        // } else {
        //     $this->reply('wtf?');
        // }
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
        $id = $this->message->id();

        // $this->chat->deleteMessage($id);
        // dd($id);
        $this->chat->html("Received: $text " . $id)->send();
        $this->chat->message('hi')->withData('caption', 'test')->send();
    }
}
