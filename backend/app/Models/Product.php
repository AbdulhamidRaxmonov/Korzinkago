<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'name_ru', 'slug', 'description',
        'image', 'images', 'price', 'old_price', 'unit', 'step',
        'stock', 'barcode', 'is_active', 'is_featured', 'sold_count',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'float',
        'old_price' => 'float',
        'step' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sold_count' => 'integer',
    ];

    protected $appends = ['has_discount', 'discount_percent', 'in_stock'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Rasmni to'liq URL ko'rinishida qaytarish.
     * Yuklangan fayl bo'lsa /storage/... ga aylantiriladi, URL bo'lsa o'zgarmaydi.
     */
    public function getImageAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return rtrim(config('app.url'), '/').'/storage/'.ltrim($value, '/');
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->old_price !== null && $this->old_price > $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->has_discount) {
            return 0;
        }

        return (int) round(100 - ($this->price / $this->old_price * 100));
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
