<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'address', 'lat', 'lng', 'phone',
        'open_at', 'close_at', 'is_active',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
