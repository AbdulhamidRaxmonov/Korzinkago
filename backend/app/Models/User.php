<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'avatar',
        'is_active', 'is_online', 'current_lat', 'current_lng', 'vehicle_type',
        'phone_verified_at', 'rating', 'reviews_count',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'current_lat' => 'float',
            'current_lng' => 'float',
        ];
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Kuryer sifatida olgan baholar */
    public function courierReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'courier_id')->where('type', 'courier');
    }
}
