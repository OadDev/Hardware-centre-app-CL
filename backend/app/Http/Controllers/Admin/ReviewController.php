<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\ReviewAggregator;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->with(['customer', 'product']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($rating = $request->query('rating')) {
            $query->where('rating', $rating);
        }

        $reviews = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));
        $reviews->getCollection()->transform(fn (Review $r) => $this->transform($r));

        return response()->json($reviews);
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Approved,Rejected'],
        ]);

        $review->update($data);
        ReviewAggregator::recalculate($review->product);

        return response()->json(['review' => $this->transform($review->fresh(['customer', 'product']))]);
    }

    public function destroy(Review $review)
    {
        $product = $review->product;
        $review->delete();
        ReviewAggregator::recalculate($product);

        return response()->json(['message' => 'Review deleted']);
    }

    private function transform(Review $r): array
    {
        return [
            'id' => $r->id,
            'customerName' => $r->customer->name,
            'product' => $r->product->name,
            'productCode' => $r->product->product_code,
            'rating' => $r->rating,
            'title' => $r->title,
            'text' => $r->body,
            'verified' => $r->verified_purchase,
            'status' => $r->status,
            'createdAt' => $r->created_at->toIso8601String(),
        ];
    }
}
