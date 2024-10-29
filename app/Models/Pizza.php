<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Pizza extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    protected $table = 'pizzas';

    public $translatedAttributes = [
        'name',
        'description',
        'detail',
    ];

    protected $fillable = [
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

    public function scopeWithSizesTranslation($query)
    {
        $query->with('sizes', function ($query) {
            $query->withTranslation();
        });
    }

    public function recommendedPorducts(): BelongsToMany
    {
        return $this->belongsToMany(RecommendedProduct::class, 'recommended_products');
    }
}
