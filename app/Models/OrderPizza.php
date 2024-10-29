<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderPizza extends Model
{
    use HasFactory;

    protected $table = 'order_pizzas';

    protected $fillable = [
        'order_id',
        'pizza_id',
        'pizza_size_id',
        'quantities',
        'count',
    ];

    public function size(): HasOne
    {
        return $this->hasOne(PizzaSize::class, 'id', 'pizza_size_id');
    }

    public function pizza(): HasOne
    {
        return $this->hasOne(Pizza::class, 'id', 'pizza_id');
    }

    public function scopeWithSizeTranslation($query)
    {
        $query->with(['size' => function ($query) {
            $query->withTranslation();
        }]);
    }

    public function scopeWithPizzaTranslation($query)
    {
        $query->with(['pizza' => function ($query) {
            $query->withTranslation();
        }]);
    }
}
