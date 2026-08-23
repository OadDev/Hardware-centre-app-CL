<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Support\ReviewAggregator;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    private const TITLES = [
        'Good value for the price', 'Works as expected', 'Solid quality', 'Would recommend',
        'Does the job', 'Better than I expected', 'Decent, some issues', 'Exactly what I needed',
    ];

    private const BODIES = [
        'Genuine product, delivered on time. Packaging was solid and the price was better than my local market.',
        'Good quality and works as described. Would order again for my next site project.',
        'Been using this for a few weeks now on-site — holding up well so far.',
        'Matches the listing description. Delivery was a bit slow but the product itself is fine.',
        'Ordered in bulk for a project, all pieces were consistent and well packed.',
        'Decent quality for the price. Nothing extraordinary but gets the job done.',
        'Had a minor issue with one unit but the seller sorted it out quickly.',
        'Exactly what our team needed for the job. Will buy again.',
        'Packaging could be better but the product itself works fine.',
        'Reliable brand, no complaints. Fast delivery to our site address.',
        'Used it for a home renovation, easy to work with and good finish.',
        'Value for money. Compared a few options and this one held up best.',
        'Arrived a day earlier than expected, quality looks genuine.',
        'Not bad, but I expected slightly better finishing for this price.',
        'Second time ordering this — consistent quality both times.',
    ];

    public function run(): void
    {
        $customers = Customer::all();
        if ($customers->isEmpty()) {
            return;
        }

        $this->seedFromRealOrders($customers);

        Product::all()->each(function (Product $product) use ($customers) {
            $alreadyReviewed = $product->reviews()->pluck('customer_id');
            $pool = $customers->whereNotIn('id', $alreadyReviewed);
            if ($pool->isEmpty()) {
                return;
            }

            $reviewerCount = random_int(2, 7);
            $reviewers = $pool->random(min($reviewerCount, $pool->count()));

            foreach ($reviewers as $customer) {
                $this->createReview($customer, $product);
            }
        });

        Product::all()->each(fn (Product $product) => ReviewAggregator::recalculate($product));
    }

    /**
     * The demo customer (rajesh@kumarhardware.in) has a real Delivered order
     * in OrderSeeder — attach genuine verified-purchase reviews to those
     * order items so the "Verified Purchase" badge has at least one real
     * example wired end to end, not just seeded-looking data.
     */
    private function seedFromRealOrders($customers): void
    {
        $demo = $customers->firstWhere('email', 'rajesh@kumarhardware.in');
        if (! $demo) {
            return;
        }

        $deliveredItems = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('customer_id', $demo->id)->where('status', 'Delivered'))
            ->get();

        foreach ($deliveredItems as $item) {
            $this->createReview($demo, $item->product, $item->id, true);
        }
    }

    private function createReview(Customer $customer, Product $product, ?int $orderItemId = null, bool $verified = false): void
    {
        Review::updateOrCreate(
            ['customer_id' => $customer->id, 'product_id' => $product->id],
            [
                'order_item_id' => $orderItemId,
                'rating' => $this->weightedRating(),
                'title' => fake()->boolean(60) ? fake()->randomElement(self::TITLES) : null,
                'body' => fake()->randomElement(self::BODIES),
                'verified_purchase' => $verified,
                'status' => 'Approved',
            ]
        );
    }

    /**
     * Real marketplaces skew heavily toward 4-5 stars — mirror that instead
     * of a flat random 1-5, so the seeded rating distribution looks genuine.
     */
    private function weightedRating(): int
    {
        return fake()->randomElement([5, 5, 5, 5, 5, 4, 4, 4, 4, 3, 3, 2, 1]);
    }
}
