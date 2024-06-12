<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendedProduct extends Model
{
    use HasFactory;

    protected $table = 'recommended_products';

    protected $fillable = [
        'pizza_id',
        'product_id',
    ];
}
