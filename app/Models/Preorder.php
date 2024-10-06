<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preorder extends Model
{
    use HasFactory;

    protected $table = 'preorders';

    protected $fillable = [
        'pizza',
    ];

    public function casts(): array
    {
        return [
            'pizza' => 'array',
        ];
    }
}
