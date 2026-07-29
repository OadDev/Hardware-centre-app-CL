<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = [
        'seller_code', 'user_id', 'store_name', 'owner_name', 'email', 'phone',
        'city', 'state', 'warehouse_address', 'category_interest', 'gst_number', 'commission_pct',
        'wallet_balance', 'rating', 'approval_status', 'account_status',
        'kyc_status', 'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_pct' => 'decimal:2',
            'wallet_balance' => 'decimal:2',
            'rating' => 'decimal:1',
            'joined_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'seller_code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }
}
