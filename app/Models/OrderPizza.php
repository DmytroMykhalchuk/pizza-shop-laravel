<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPizza extends Model
{
    use HasFactory;

    protected $table = 'order_pizzas';

    protected $fillable = [];

    protected $guarded = [
        'order_id',
        'pizza_id',
        'pizza_size_id',
        'quantities',
        'count',
    ];
}
