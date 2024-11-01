<?php

namespace App\Http\Controllers\Pizza;

use App\Actions\Pizza\PizzaAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Pizza\PizzaSearchRequest;
use Illuminate\Http\JsonResponse;

class PizzaController extends BaseController
{
    private PizzaAction $pizzaAction;

    public function __construct()
    {
        $this->pizzaAction = new PizzaAction();
    }

    public function search(PizzaSearchRequest $pizzaSearchRequest): JsonResponse
    {
        $data = $this->pizzaAction->search(
            $pizzaSearchRequest->getSearch(),
        );

        return $this->formatResponse($data);
    }
}
