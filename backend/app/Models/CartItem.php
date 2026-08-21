<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['customer_id', 'product_id', 'product_variant_id', 'quantity'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getUnitPriceAttribute(): float
    {
        if ($this->productVariant) {
            return $this->productVariant->priceForQuantity($this->quantity);
        }

        return $this->product->priceForQuantity($this->quantity);
    }
}
