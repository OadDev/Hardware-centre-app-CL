<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    protected $fillable = [
        'customer_id', 'product_id', 'product_variant_id', 'quantity',
        'unit_price', 'cart_value', 'status', 'abandoned_at', 'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cart_value' => 'decimal:2',
            'abandoned_at' => 'datetime',
            'purchased_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
