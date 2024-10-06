<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function sizes(): HasMany
    {
        return $this->hasMany(PizzaSize::class);
    }

    public function recommendedPorducts(): BelongsToMany
    {
        return $this->belongsToMany(RecommendedProduct::class, 'recommended_products');
    }
}
