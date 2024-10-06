<?php

namespace App\Actions\Payments;

use App\Http\Services\MonobankService\MonobankService;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentsAction
{
    private MonobankService $monobankService;

    public function __construct()
    {
        $this->monobankService = new MonobankService();
    }

    public function getPublicKey()
    {
        return $this->monobankService->getPublicKey();
    }

    public function monobankHandler(array $response, string $monobankSign)
    {
        Log::info($response);

        $order = Order::where('invoice_id', $response['invoiceId'])->first();

        $successStatus = 'success';
        $pubKeyBase64 = env('MONOBANK_PUBLICK_KEY');
        $xSignBase64 = $monobankSign;

        if (!$order) {
            Log::error('Payment not found');
            return http_response_code(200);
        }

        if (!$monobankSign) {
            Log::error('Sign key not found');
            return http_response_code(200);
        }

        if ($response['status'] !== $successStatus) {
            return http_response_code(200);
        }

        if ($pubKeyBase64) {
            $message = json_encode($response);
            $signature = base64_decode($xSignBase64);
            $publicKey = openssl_get_publickey(base64_decode($pubKeyBase64));

            $result = openssl_verify($message, $signature, $publicKey, OPENSSL_ALGO_SHA256);
            if ($result === 1) {
                $order->paid_at = Carbon::parse($response['modifiedDate']);
                $order->save();
            } else {
            }
        } else if ($response['status'] === $successStatus) {
            $order->paid_at = Carbon::parse($response['modifiedDate']);
            $order->save();
        }
        return http_response_code(200);
    }
}
