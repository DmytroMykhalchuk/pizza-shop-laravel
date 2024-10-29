<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PizzaTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\Translations/PizzaTranslationFactory> */
    use HasFactory;

    protected $table = 'pizza_translations';

    protected $fillable = [
        'name',
        'description',
        'detail',
    ];

    public $timestamps = false;
}
