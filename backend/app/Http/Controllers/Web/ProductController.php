<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    private const SAMPLE_REVIEWS = [
        ['name' => 'Amit Sharma', 'init' => 'A', 'date' => '2 weeks ago', 'stars' => 5, 'text' => 'Genuine product, delivered on time. Packaging was solid and price was better than my local market.'],
        ['name' => 'Priya Verma', 'init' => 'P', 'date' => '1 month ago', 'stars' => 4, 'text' => 'Good quality and works as described. Would order again for my next site project.'],
    ];

    private const RATING_BREAKDOWN = [
        ['stars' => 5, 'pct' => 78],
        ['stars' => 4, 'pct' => 54],
        ['stars' => 3, 'pct' => 22],
        ['stars' => 2, 'pct' => 8],
    ];

    public function show(Product $product)
    {
        abort_unless($product->approval_status === 'Approved', 404);

        $product->load(['brand', 'category', 'seller', 'specs', 'images', 'variants']);

        $related = Product::query()->approved()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['brand', 'category', 'images'])
            ->orderByDesc('reviews_count')
            ->limit(4)
            ->get();

        return view('web.product', [
            'product' => $product,
            'related' => $related,
            'reviews' => self::SAMPLE_REVIEWS,
            'ratingBreakdown' => self::RATING_BREAKDOWN,
        ]);
    }
}
