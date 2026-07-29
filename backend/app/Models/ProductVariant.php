<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'variant_code', 'sku', 'options', 'price', 'stock', 'status'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'variant_code';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * MySQL's JSON column doesn't guarantee key order is preserved on
     * round-trip, so build the label in a fixed, predictable order rather
     * than trusting array_values() on the raw decoded object.
     */
    public function getLabelAttribute(): string
    {
        $options = $this->options ?? [];
        $ordered = [];

        foreach (['Color', 'Size'] as $key) {
            if (array_key_exists($key, $options)) {
                $ordered[] = $options[$key];
            }
        }
        foreach ($options as $key => $value) {
            if (! in_array($key, ['Color', 'Size'], true)) {
                $ordered[] = $value;
            }
        }

        return implode(' / ', $ordered);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product->price);
    }
}
