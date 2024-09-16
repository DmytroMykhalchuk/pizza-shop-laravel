<?php

namespace App\Http\Controllers\TelegramWebhook;

use App\Actions\TelegramWebhook\TelegramWebhookAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\TelegramWebhook\SetWebhookRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelegramWebhookController extends BaseController
{
    private TelegramWebhookAction $webhookAction;

    public function __construct()
    {
        $this->webhookAction = new TelegramWebhookAction();
    }

    public function index(Request $request)
    {
        // Cache::forever('wd', $request->all());

        return response(null, Response::HTTP_OK);
    }

    public function setWebhook()
    {
        $url = env('TELEGRAM_WEBHOOK_URL');
        $this->webhookAction->setWebhook($url);
    }
}
