<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PizzaIngredient extends Model
{
    use HasFactory;

    protected $table = 'pizza_ingredients';

    protected $fillable = [
        'pizza_id',
        'ingredient_id',
        'quantity',
    ];

}
