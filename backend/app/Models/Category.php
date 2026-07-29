<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'category_code', 'name', 'slug', 'icon', 'icon_path', 'sort_order', 'status',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'category_code';
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? Storage::disk('uploads')->url($this->icon_path) : null;
    }
}
