<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'usage_limit', 'used_count', 'expires_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order' => 'decimal:2',
            'expires_at' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function discountFor(float $subtotal): float
    {
        if ($this->type === 'Percentage') {
            return round($subtotal * ((float) $this->value / 100), 2);
        }

        return min((float) $this->value, $subtotal);
    }
}
