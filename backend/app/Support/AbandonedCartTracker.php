<?php

namespace App\Support;

use App\Models\AbandonedCart;
use App\Models\CartItem;

/**
 * A cart is "abandoned" once it has sat untouched past the configured
 * threshold without the customer checking out. Since this app has no
 * guaranteed cron/queue worker on its shared host, sweep() is called
 * synchronously wherever the admin actually needs a fresh view (the
 * Abandoned Carts list) rather than relying solely on a scheduled job —
 * the stale-cart scan below is cheap enough to run on that request. The
 * artisan command in App\Console\Commands\SweepAbandonedCarts wraps the
 * same sweep() for hosts that do have a working cron, as a belt-and-braces
 * addition, not a requirement.
 */
class AbandonedCartTracker
{
    public static function thresholdMinutes(): int
    {
        return (int) config('cart.abandoned_after_minutes', 30);
    }

    /**
     * Finds cart items that have been idle past the threshold and
     * upserts one open "Abandoned" record per (customer, product,
     * variant) — repeated sweeps just refresh the quantity/value on the
     * same still-abandoned row instead of duplicating it. Guest
     * checkouts never reach here since they have no persisted cart.
     */
    public static function sweep(): void
    {
        $cutoff = now()->subMinutes(self::thresholdMinutes());

        CartItem::query()
            ->with(['customer', 'product', 'productVariant'])
            ->whereHas('customer', fn ($q) => $q->where('is_guest', false))
            ->where('updated_at', '<=', $cutoff)
            ->get()
            ->each(function (CartItem $item) {
                if (! $item->customer || ! $item->product) {
                    return;
                }

                $unitPrice = (float) $item->unit_price;

                AbandonedCart::updateOrCreate(
                    [
                        'customer_id' => $item->customer_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'status' => 'Abandoned',
                    ],
                    [
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'cart_value' => round($unitPrice * $item->quantity, 2),
                        'abandoned_at' => $item->updated_at,
                    ]
                );
            });
    }

    /**
     * Flips any open abandoned record for this exact product/variant to
     * Purchased the moment the customer actually checks out — called
     * from order placement, not from the sweep, so conversion is
     * reflected immediately rather than waiting for the next sweep.
     */
    public static function markPurchased(int $customerId, int $productId, ?int $variantId): void
    {
        AbandonedCart::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->where('status', 'Abandoned')
            ->where(function ($q) use ($variantId) {
                $variantId ? $q->where('product_variant_id', $variantId) : $q->whereNull('product_variant_id');
            })
            ->update(['status' => 'Purchased', 'purchased_at' => now()]);
    }
}
