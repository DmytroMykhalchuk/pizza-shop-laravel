<?php

namespace App\Models;

use DefStudio\Telegraph\DTO\Chat;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public const MONOBANK_TYPE = 'mono';

    public const PAYMENT_TYPES = [
        self::ONLINE_TYPE,
        self::OFFLINE_TYPE,
    ];

    public const STATUS_WAITING = 'waiting';
    public const STATUS_IN_ROAD = 'in_road';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'orders';

    protected $fillable = [
        'delivery_type',
        'payment_type',
        'paid_at',
        'invoice_link',
        'invoice_id',
        'status',
        'total',
    ];

    protected $guarded = [
        'telegraph_chat_id',
        'message_id',
    ];

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class, 'id', 'telegraph_chat_id');
    }

    protected function orderId(): Attribute
    {
        return Attribute::make(
            get: fn() => str_pad($this->id, 3, '0', STR_PAD_LEFT),
        );
    }

    public function pizzas(): HasMany
    {
        return $this->hasMany(OrderPizza::class, 'order_id', 'id');
    }
}
