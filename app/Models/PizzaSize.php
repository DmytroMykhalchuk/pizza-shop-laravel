<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class PizzaSize extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    protected $table = 'pizza_sizes';

    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'pizza_id',
        'price_multiplier',
        'diameter_cm',
        'weight_multiplier',
        'size_code',
    ];
}
