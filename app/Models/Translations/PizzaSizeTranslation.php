<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PizzaSizeTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\Translations/PizzaSizeTranslationsFactory> */
    use HasFactory;

    protected $table = 'pizza_size_translations';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
