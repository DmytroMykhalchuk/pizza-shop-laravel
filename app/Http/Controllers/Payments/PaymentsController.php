<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\PaymentsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    private PaymentsAction $paymentAction;

    public function __construct()
    {
        $this->paymentAction = new PaymentsAction();
    }

    public function loadPublicKey()
    {
        return $this->paymentAction->getPublicKey();
    }

    public function monobankHandler(Request $request)
    {
        $monobankSign = $request->header('X-Sign');

        return $this->paymentAction->monobankHandler(
            $request->all(),
            $monobankSign,
        );
    }
}
