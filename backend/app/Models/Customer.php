<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthenticatableModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Customer extends AuthenticatableModel
{
    use HasApiTokens;

    protected $fillable = [
        'customer_code', 'name', 'email', 'phone', 'password', 'business_name',
        'gst_number', 'city', 'state', 'wallet_balance', 'status', 'is_guest',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'is_guest' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
