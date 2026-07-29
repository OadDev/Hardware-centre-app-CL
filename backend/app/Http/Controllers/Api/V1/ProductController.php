<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const SAMPLE_REVIEWS = [
        ['name' => 'Amit Sharma', 'init' => 'A', 'date' => '2 weeks ago', 'stars' => 5, 'text' => 'Genuine product, delivered on time. Packaging was solid and price was better than my local market.'],
        ['name' => 'Priya Verma', 'init' => 'P', 'date' => '1 month ago', 'stars' => 4, 'text' => 'Good quality and works as described. Would order again for my next site project.'],
    ];

    public function index(Request $request)
    {
        $query = Product::query()->approved()->with(['brand', 'category', 'seller', 'specs', 'images']);

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
        $product->load(['brand', 'category', 'seller', 'specs', 'images']);

        return response()->json([
            'product' => new ProductResource($product),
            'reviews' => self::SAMPLE_REVIEWS,
            'rating_breakdown' => [
                ['stars' => 5, 'pct' => 78],
                ['stars' => 4, 'pct' => 54],
                ['stars' => 3, 'pct' => 22],
                ['stars' => 2, 'pct' => 8],
            ],
        ]);
    }
}
