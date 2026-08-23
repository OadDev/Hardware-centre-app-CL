<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\AppSetting;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Support\ReviewAggregator;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->approved()->with(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers', 'priceTiers']);

        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$q}%"));
            });
        }

        if ($cat = $request->query('category')) {
            $query->whereHas('category', fn ($c) => $c->where('icon', $cat));
        }

        if ($brands = $request->query('brands')) {
            $brandNames = array_filter(array_map('trim', explode(',', $brands)));
            if ($brandNames) {
                $query->whereHas('brand', fn ($b) => $b->whereIn('name', $brandNames));
            }
        }

        if ($supplier = $request->query('supplier')) {
            $query->whereHas('seller', fn ($s) => $s->where('store_name', $supplier));
        }

        match ($request->query('sort', 'pop')) {
            'low' => $query->orderBy('price'),
            'high' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('reviews_count'),
        };

        return ProductResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers', 'priceTiers']);

        $reviews = $product->reviews()->approved()->with('customer')
            ->orderByDesc('created_at')->limit(20)->get()
            ->map(fn (Review $r) => ReviewController::transform($r));

        $customer = $request->user();
        $myReview = null;
        $canReview = false;
        if ($customer) {
            $mine = $product->reviews()->where('customer_id', $customer->id)->first();
            $myReview = $mine ? ReviewController::transform($mine) : null;
            $canReview = ! $mine && OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id)->where('status', 'Delivered'))
                ->exists();
        }

        return response()->json([
            'product' => new ProductResource($product),
            'reviews' => $reviews,
            'rating_breakdown' => ReviewAggregator::breakdown($product),
            'my_review' => $myReview,
            'can_review' => $canReview,
            'contact_number' => AppSetting::current()->contact_number,
        ]);
    }
}
