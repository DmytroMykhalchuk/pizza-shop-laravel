<?php

namespace App\Http\Services\MonobankService;

use App\Models\Payment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MonobankService
{
    private Client $client;
    private string $urlPrefix = '/api';
    private int $linkActiveForHours = 12;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.monobank.ua',
        ]);
    }

    public function createInvoice(float $amount)
    {
        $headers = [
            'x-token' => env('MONOBANK_API_KEY'),
        ];
        $payload = [
            'amount' => round((float)$amount * 100),
            'redirectUrl' => 'https://t.me/dm_pizza_bot',
            'webHookUrl' => env('APP_URL') . '/api/payments/monobank/webhook',
            'validity' => $this->linkActiveForHours * 3600,
        ];
        Log::info($payload);

        $url = $this->urlPrefix . '/merchant/invoice/create?';

        try {
            $response = $this->client->post($url, [
                'json' => $payload,
                'headers' => $headers,
            ]);

            $content = $response->getBody()->getContents();
            return json_decode($content);
        } catch (\Exception $e) {
            Log::error('Monobank errro creating invoice: ' . $e->getMessage());
            $e->getMessage();
            return (object)[];
        }
    }

    public function invalidInvoiceId(string $invoiceId): bool
    {
        $headers = [
            'x-token' => env('MONOBANK_API_KEY'),
            'x-cms' => env('APP_NAME'),
        ];

        $payload = [
            'invoiceId' => $invoiceId,
        ];

        $url = $this->urlPrefix . '/merchant/invoice/remove?';
        try {
            $this->client->post($url, [
                'json' => $payload,
                'headers' => $headers,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Monobank errro creating invoice: ' . $e->getMessage());
            $e->getMessage();
            return false;
        }
    }

    public function cancelInvoice(string $invoiceId): bool
    {
        $headers = [
            'x-token' => env('MONOBANK_API_KEY'),
            'x-cms' => env('APP_NAME'),
        ];

        $payload = [
            'invoiceId' => $invoiceId,
        ];

        $url = $this->urlPrefix . '/merchant/invoice/cancel?';
        try {
            $this->client->post($url, [
                'json' => $payload,
                'headers' => $headers,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Monobank error canceling payment: ' . $e->getMessage());
            $e->getMessage();
            return false;
        }
    }

    public function getPublicKey()
    {
        $headers = [
            'x-token' => env('MONOBANK_API_KEY'),
            'x-cms' => env('APP_NAME'),
        ];


        $url = $this->urlPrefix . '/merchant/pubkey';
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);

            $cotnent = $response->getBody()->getContents();
            return $cotnent;
        } catch (\Exception $e) {
            Log::error('Monobank error : ' . $e->getMessage());
            $e->getMessage();
            return false;
        }
    }

    public function getPdfInvoice(string $invoiceId)
    {
        $headers = [
            'x-token' => env('MONOBANK_API_KEY'),
            'x-cms' => env('APP_NAME'),
        ];

        $url = $this->urlPrefix . '/merchant/invoice/receipt?invoiceId=' . $invoiceId;
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Monobank error pdf invoce retreiving: ' . $e->getMessage());
            $e->getMessage();
            return (object)[];
        }
    }
}
