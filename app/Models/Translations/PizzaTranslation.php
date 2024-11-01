<?php

namespace App\Models\Translations;

use App\Models\Pizza;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function pizza(): BelongsTo
    {
        return $this->belongsTo(Pizza::class, 'id', 'pizza_id');
    }

    public function scopeWithPizzaSize($query): void
    {
        $query->with(['pizza' => function ($query) {
            $query->with('sizes');
        }]);
    }
}
