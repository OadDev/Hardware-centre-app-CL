<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Review;

/**
 * The rating/reviews_count shown on a product are derived entirely from its
 * Approved reviews — never set directly — so recalculate() is the only
 * place those two columns are written. Call it after any create, update,
 * status change, or delete on a product's reviews.
 */
class ReviewAggregator
{
    public static function recalculate(Product $product): void
    {
        $approved = Review::query()->approved()->where('product_id', $product->id);

        $count = (clone $approved)->count();
        $avg = $count > 0 ? (clone $approved)->avg('rating') : 0;

        $product->update([
            'rating' => $count > 0 ? round($avg, 1) : 0,
            'reviews_count' => $count,
        ]);
    }

    /**
     * Star-by-star breakdown of a product's Approved reviews, as the
     * percentage of the total that gave each star rating (5 down to 1).
     */
    public static function breakdown(Product $product): array
    {
        $counts = Review::query()->approved()->where('product_id', $product->id)
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $totalReviews = $counts->sum();

        return collect(range(5, 1))->map(fn ($stars) => [
            'stars' => $stars,
            'pct' => $totalReviews > 0 ? (int) round((($counts[$stars] ?? 0) / $totalReviews) * 100) : 0,
        ])->all();
    }
}
