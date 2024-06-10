<?php

namespace App\Http\Controllers\TelegramWebhook;

use App\Actions\TelegramWebhook\TelegramWebhookAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\TelegramWebhook\SetWebhookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookController extends BaseController
{
    private TelegramWebhookAction $webhookAction;

    public function __construct()
    {
        $this->webhookAction = new TelegramWebhookAction();
    }

    public function index(Request $request)
    {
        Cache::forever('wd', $request->all());
        return 200;
    }

    public function setWebhook(SetWebhookRequest $setWebhookRequest)
    {
        $url = $setWebhookRequest->getUrl();
        $this->webhookAction->setWebhook($url);
    }
}
