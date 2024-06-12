<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pizza extends Model
{
    use HasFactory;

    protected $table = 'pizzas';

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'image',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'pizza_ingredients')->withPivot('quantity');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(PizzaSize::class, 'pizza_sizes');
    }

    public function recommendedPorducts(): BelongsToMany
    {
        return $this->belongsToMany(RecommendedProduct::class, 'recommended_products');
    }
}
