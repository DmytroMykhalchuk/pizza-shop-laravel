<?php

namespace App\Http\Telegram\Actions\Screen;

use App\Http\Telegram\Actions\AbstractAction;
use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\InlineQueryResultArticle;
use DefStudio\Telegraph\DTO\InlineQueryResultPhoto;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class ScreenAction extends AbstractAction
{
    private TelegraphChat $chat;

    private string $defaultLocale = 'en';

    public function setChat(TelegraphChat $chat)
    {
        $this->chat = $chat;
        app()->setLocale($this->chat->locale);
    }

    public function start($message): void
    {
        $translation = [
            'title' => __('main.intro_title'),
            'text'  => __('main.intro_description'),
        ];

        $this->saveUser($message->from());

        $caption = $translation['title'] . "\n\n\n" . $translation['text'];
        $caption .= "\n\n\nFor testing payment use card 424242... with any other properties :)";

        $messageId = '';
        $keyboard = $this->getPreviewKeyboard($this->chat, $messageId);

        $response = $this->chat
            ->photo($this->introImage)
            ->html($caption)
            ->keyboard($keyboard)
            ->send();

        $messageId = $response->telegraphMessageId();
        $keyboard = $this->getPreviewKeyboard($this->chat, $messageId);
        $this->chat->replaceKeyboard($messageId, $keyboard)->send();

        Telegraph::setChatMenuButton()->webApp("Pizza 🍕", env('FRONTEND_URL'))->send();
    }

    public function handleInlineQuery(InlineQuery $inlineQuery, $bot): void
    {
        $query = $inlineQuery->query();
        $query = (int)$query;

        $response = $bot->answerInlineQuery($inlineQuery->id(), [
            InlineQueryResultArticle::make(random_int(2, 1000), 'Title', '/rows')->thumbUrl(asset('assets/images/pepperoni.png'))->description('fd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/pepperoni.png'), asset('assets/images/pepperoni.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
            InlineQueryResultPhoto::make(random_int(2, 1000), asset('assets/images/margarita-min.png'), asset('assets/images/margarita-min.png'))
                ->caption('Light Logo')->width(100)->height(100)->title('title')->description('dfd'),
        ])->send();
    }

    public function showPreview(string $messageId)
    {
        $this->toPreview($this->chat, $messageId);
    }

    private function saveUser($user): void
    {
        $this->chat->user_id = $user->id();
        $this->chat->username = $user->id();
        $this->chat->first_name = $user->firstName();
        $this->chat->last_name = $user->lastName();
        $this->chat->username = $user->username();

        $this->chat->locale = isset($this->chat->locale)
            ? $this->chat->locale
            : $user->languageCode() ?? $this->defaultLocale;

        $this->chat->save();

        app()->setLocale($this->chat->locale);
    }
}
