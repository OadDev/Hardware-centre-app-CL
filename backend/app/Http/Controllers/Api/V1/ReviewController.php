<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Support\ReviewAggregator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $reviews = $product->reviews()->approved()
            ->with('customer')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        $reviews->getCollection()->transform(fn (Review $r) => self::transform($r));

        return response()->json([
            'reviews' => $reviews,
            'rating_breakdown' => ReviewAggregator::breakdown($product),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $customer = $request->user();

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id)->where('status', 'Delivered'))
            ->latest('id')
            ->first();

        if (! $orderItem) {
            throw ValidationException::withMessages([
                'rating' => 'You can review a product once it has been delivered to you.',
            ]);
        }

        $review = Review::updateOrCreate(
            ['customer_id' => $customer->id, 'product_id' => $product->id],
            [
                'order_item_id' => $orderItem->id,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'verified_purchase' => true,
                'status' => 'Approved',
            ]
        );

        ReviewAggregator::recalculate($product);

        return response()->json(['review' => self::transform($review->fresh('customer'))]);
    }

    public function destroy(Request $request, Product $product)
    {
        $product->reviews()->where('customer_id', $request->user()->id)->delete();

        ReviewAggregator::recalculate($product);

        return response()->json(['message' => 'Review deleted']);
    }

    public static function transform(Review $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->customer->name ?: 'Anonymous',
            'init' => mb_strtoupper(mb_substr($r->customer->name ?: 'A', 0, 1)),
            'date' => $r->created_at->diffForHumans(),
            'stars' => $r->rating,
            'title' => $r->title,
            'text' => $r->body,
            'verified' => $r->verified_purchase,
        ];
    }
}
