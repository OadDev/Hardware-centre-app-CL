<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $this */
        $customer = $request->user();
        $wished = false;
        if ($customer) {
            $wished = $this->relationLoaded('wishlistedBy')
                ? $this->wishlistedBy->isNotEmpty()
                : $customer->wishlist()->where('product_id', $this->id)->exists();
        }

        return [
            'id' => $this->product_code,
            'sku' => $this->sku,
            'name' => $this->name,
            'brand' => $this->brand->name,
            'cat' => $this->category->icon,
            'category_name' => $this->category->name,
            'price' => (float) $this->price,
            'mrp' => (float) $this->mrp,
            'discount_pct' => $this->discount_pct,
            'rating' => (float) $this->rating,
            'reviews' => $this->reviews_count,
            'supplier' => $this->seller->store_name,
            'seller_id' => $this->seller->seller_code,
            'seller_rating' => (float) $this->seller->rating,
            'seller_years' => $this->seller->joined_at ? (int) now()->diffInYears($this->seller->joined_at, true) : 0,
            'loc' => $this->seller->city,
            'eta' => $this->eta_days,
            'unit' => $this->unit,
            'tag' => $this->tag ?? '',
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
            'featured' => (bool) $this->featured,
            'wished' => $wished,
            'specs' => $this->specs->map(fn ($s) => [$s->label, $s->value])->all(),
            'desc' => $this->description,
            'images' => $this->images->pluck('url')->filter()->values()->all(),
            'video_url' => $this->video_url,
            'variants' => $this->variants->where('status', 'Active')->values()->map(fn ($v) => [
                'id' => $v->variant_code,
                'sku' => $v->sku,
                'label' => $v->label,
                'image' => $v->image_url,
                'price' => $v->effective_price,
                'stock' => $v->stock,
                'price_tiers' => $v->priceTiers->map(fn ($t) => [
                    'min_qty' => $t->min_qty,
                    'price' => (float) $t->price,
                ])->all(),
            ])->all(),
            'apply_bulk_pricing_to_all_variants' => (bool) $this->apply_bulk_pricing_to_all_variants,
            'price_tiers' => $this->priceTiers->map(fn ($t) => [
                'min_qty' => $t->min_qty,
                'price' => (float) $t->price,
            ])->all(),
        ];
    }
}
