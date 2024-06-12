<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const COURIER_TYPE = 'courier';
    public const IN_STORE_TYPE = 'store';

    public const DELIVERY_TYPES = [
        self::COURIER_TYPE,
        self::IN_STORE_TYPE,
    ];

    public const ONLINE_TYPE = 'online';
    public const OFFLINE_TYPE = 'offline';

    public const PAYMENT_TYPES = [
        self::ONLINE_TYPE,
        self::OFFLINE_TYPE,
    ];


    protected $table = 'orders';

    protected $fillable = [
        'is_paid',
        'delivery_type',
        'payment_type',
        'is_paid',
        'paid_at',
    ];

    protected $guarded = [
        'telegraph_chat_id',
    ];
}
