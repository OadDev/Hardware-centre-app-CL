<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'product_code', 'sku', 'name', 'slug', 'category_id', 'brand_id',
        'seller_id', 'hsn_code_id', 'description', 'video_url', 'price', 'mrp',
        'unit', 'stock', 'approval_status', 'featured', 'rating', 'reviews_count',
        'sold_count', 'eta_days', 'tag',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'rating' => 'decimal:1',
            'featured' => 'boolean',
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'Approved');
    }

    public function getRouteKeyName(): string
    {
        return 'product_code';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function hsnCode(): BelongsTo
    {
        return $this->belongsTo(HsnCode::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'wishlists');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        }

        return $this->stock < 20 ? 'Low Stock' : 'In Stock';
    }

    public function getDiscountPctAttribute(): int
    {
        if ((float) $this->mrp <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->price / (float) $this->mrp)) * 100);
    }
}
