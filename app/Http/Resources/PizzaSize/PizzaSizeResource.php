<?php

namespace App\Http\Resources\PizzaSize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PizzaSizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'pizzaId'          => $this->pizza_id,
            'priceMultiplier'  => (float) $this->price_multiplier,
            'diameterCm'       => (float) $this->diameter_cm,
            'weightMultiplier' => (float) $this->weight_multiplier,
            'sizeCode'         => $this->size_code,
        ];
    }
}
