<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preorder extends Model
{
    use HasFactory;

    protected $table = 'preorders';

    protected $fillable = [
        'user_id',
        'pizzas',
        'products',
        'address',
    ];

    public function casts(): array
    {
        return [
            'pizzas'   => 'array',
            'products' => 'array',
        ];
    }
}
