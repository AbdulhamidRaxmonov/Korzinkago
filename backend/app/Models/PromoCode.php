<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_discount',
        'usage_limit', 'used_count', 'per_user_limit', 'first_order_only',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'float',
        'min_order' => 'float',
        'max_discount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'first_order_only' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /**
     * Berilgan summaga nisbatan chegirma miqdorini hisoblash.
     */
    public function discountFor(float $itemsTotal): float
    {
        if ($this->type === 'fixed') {
            $discount = $this->value;
        } else {
            $discount = $itemsTotal * ($this->value / 100);
            if ($this->max_discount !== null) {
                $discount = min($discount, $this->max_discount);
            }
        }

        // Chegirma buyurtma summasidan oshmasligi kerak
        return round(min($discount, $itemsTotal), 2);
    }
}
