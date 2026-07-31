<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'category_code', 'name', 'slug', 'icon', 'icon_path', 'icon_class', 'sort_order', 'status',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'category_code';
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? Storage::disk('uploads')->url($this->icon_path) : null;
    }

    /**
     * This category's id plus every descendant's id (assumes a shallow
     * two-level hierarchy — top-level category -> subcategories — which is
     * all the admin UI currently supports).
     */
    public function selfAndDescendantIds(): array
    {
        return [$this->id, ...$this->children()->pluck('id')->all()];
    }
}
