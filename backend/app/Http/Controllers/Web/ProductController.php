<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Review;
use App\Support\ReviewAggregator;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->approval_status === 'Approved', 404);

        $product->load(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers', 'priceTiers', 'hsnCode']);

        $related = Product::query()->approved()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['brand', 'category', 'images'])
            ->orderByDesc('reviews_count')
            ->limit(4)
            ->get();

        $reviews = $product->reviews()->approved()->with('customer')
            ->orderByDesc('created_at')->limit(20)->get()
            ->map(fn (Review $r) => ReviewController::transform($r));

        $offers = Coupon::where('status', 'Active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return view('web.product', [
            'product' => $product,
            'related' => $related,
            'reviews' => $reviews,
            'ratingBreakdown' => ReviewAggregator::breakdown($product),
            'contactNumber' => AppSetting::current()->contact_number,
            'gstRate' => $product->effectiveGstRate(),
            'offers' => $offers,
        ]);
    }
}
