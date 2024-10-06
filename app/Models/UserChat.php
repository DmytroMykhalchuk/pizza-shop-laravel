<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    use HasFactory;

    protected $table = 'user_chats';

    protected $fillable = [
        'telegraph_chat_id',
        'telegram_user_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
    ];
}
