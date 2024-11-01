<?php

namespace App\Http\Resources\Pizza;

use App\Http\Resources\PizzaSize\PizzaSizeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PizzaIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'detail'      => $this->detail,
            'basePrice'   => (float)$this->base_price,
            'image'       => asset($this->image),
            'sizes'       => PizzaSizeResource::collection($this->sizes),
        ];
    }
}
