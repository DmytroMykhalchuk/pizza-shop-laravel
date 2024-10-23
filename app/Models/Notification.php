<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;

    public const TYPE_PAID = 'order_paid';
    public const TYPE_DELIVERED = 'delivered';
    public const TYPE_WAIT_PAYMENT = 'wait_payment';

    public const TYPES = [
        self::TYPE_PAID,
        self::TYPE_DELIVERED,
        self::TYPE_WAIT_PAYMENT,
    ];

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'message',
        'is_checked',
        'type',
    ];

    public function casts(): array
    {
        return [
            'is_checked' => 'bool',
        ];
    }
}
