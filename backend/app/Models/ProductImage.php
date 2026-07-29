<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'sort_order', 'is_primary'];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('uploads')->url($this->path) : null;
    }

    /**
     * @param  UploadedFile[]  $files
     */
    public static function storeUploads(Product $product, array $files): void
    {
        $nextSort = (int) $product->images()->max('sort_order');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('products', 'uploads');
            $product->images()->create([
                'path' => $path,
                'sort_order' => ++$nextSort,
                'is_primary' => ! $hasPrimary,
            ]);
            $hasPrimary = true;
        }
    }
}
