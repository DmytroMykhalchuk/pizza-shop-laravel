<?php

namespace App\Actions\Pizza;

use App\Http\Resources\Pizza\PizzaIndexResource;
use App\Models\Pizza;
use App\Models\Translations\PizzaTranslation;

class PizzaAction
{
    public function __construct() {}

    public function search(string $searchText = ''): array
    {
        if(!$searchText){

        }
        $pizzaTranslationIds = PizzaTranslation::where('name', 'like', '%' . $searchText . '%')
            ->orWhere('description', 'like', '%' . $searchText . '%')
            ->orWhere('detail', 'like', '%' . $searchText . '%')
            ->get(['pizza_id'])
            ->pluck('pizza_id')->toArray();

        $pizzas = Pizza::withTranslation()->withSizesTranslation()->find($pizzaTranslationIds);

        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'Success',
            'data' => PizzaIndexResource::collection($pizzas),
        ];
    }
}
