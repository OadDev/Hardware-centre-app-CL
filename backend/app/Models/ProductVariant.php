<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'variant_code', 'sku', 'image_path', 'options', 'price', 'stock', 'status'];

    protected $appends = ['image_url'];

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

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_qty');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('uploads')->url($this->image_path) : null;
    }

    /**
     * A variant's display name is whatever the seller typed when creating
     * it (e.g. "Black", "Black - 3mm"). Older rows created before variants
     * were simplified only have the legacy Color/Size attribute map, so
     * fall back to building a label from that.
     */
    public function getLabelAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

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

        return implode(' / ', $ordered) ?: $this->sku;
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product->price);
    }

    public function priceForQuantity(int $quantity): float
    {
        // A variant with its own tier schedule uses only that schedule —
        // it doesn't matter whether apply_bulk_pricing_to_all_variants is
        // on, and a quantity below the variant's own lowest tier just means
        // no discount yet, not "fall through to the base schedule".
        $tiers = $this->priceTiers->isNotEmpty()
            ? $this->priceTiers
            : ($this->product->apply_bulk_pricing_to_all_variants ? $this->product->priceTiers : collect());

        $tier = $tiers
            ->filter(fn (ProductPriceTier $t) => $t->min_qty <= $quantity)
            ->sortByDesc('min_qty')
            ->first();

        if ($tier) {
            return (float) $tier->price;
        }

        return $this->effective_price;
    }
}
