<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'user_id', 'courier_id', 'store_id', 'status',
        'payment_method', 'payment_status',
        'delivery_address', 'delivery_lat', 'delivery_lng',
        'entrance', 'floor', 'apartment', 'comment', 'recipient_phone',
        'items_total', 'delivery_fee', 'discount', 'promo_code', 'total', 'distance_km',
        'accepted_at', 'delivered_at', 'cancelled_at', 'cancel_reason',
    ];

    protected $casts = [
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'items_total' => 'float',
        'delivery_fee' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'distance_km' => 'float',
        'accepted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const STATUSES = [
        'new', 'accepted', 'assembling', 'ready', 'on_way', 'delivered', 'cancelled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeForCourier($query)
    {
        return $query->whereIn('status', ['ready', 'on_way']);
    }

    public static function generateNumber(): string
    {
        return 'KG-'.str_pad((string) (static::max('id') + 1), 6, '0', STR_PAD_LEFT);
    }

    public function logStatus(string $status, ?int $changedBy = null, ?string $note = null): void
    {
        $this->statusLogs()->create([
            'status' => $status,
            'changed_by' => $changedBy,
            'note' => $note,
        ]);
    }
}
