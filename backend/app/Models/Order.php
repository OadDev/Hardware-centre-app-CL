<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_no', 'customer_id', 'address_id', 'coupon_id', 'subtotal',
        'discount', 'gst_amount', 'delivery_charge', 'total', 'shipping_method',
        'payment_method', 'payment_status', 'status', 'courier', 'awb',
        'placed_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }
}
