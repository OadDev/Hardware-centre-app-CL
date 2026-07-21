<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'brand_code', 'name', 'slug', 'featured', 'status',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'brand_code';
    }
}
