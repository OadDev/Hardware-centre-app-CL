<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ShippingConfig;
use Illuminate\Support\Collection;

class CartCalculator
{
    /**
     * @param  Collection<int, CartItem>  $items
     */
    public static function totals(Collection $items, ?Coupon $coupon = null, ?string $paymentMethod = null, ?string $shippingMethod = 'standard', ?string $state = null): array
    {
        $subtotal = $items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $count = $items->sum('quantity');

        $discount = 0.0;
        if ($coupon && $subtotal >= (float) $coupon->min_order) {
            $discount = $coupon->discountFor($subtotal);
        }

        $taxable = $subtotal - $discount;

        // Each product's GST rate is its HSN code's rate where one is set,
        // falling back to the store-wide default — so a coupon discount is
        // prorated across items by revenue share before each item's own
        // rate is applied, rather than taxing the whole cart at one rate.
        $items->loadMissing('product.hsnCode');
        $defaultGstRate = (float) AppSetting::current()->default_gst_rate;
        $gst = 0.0;
        if ($subtotal > 0) {
            foreach ($items as $item) {
                $lineTotal = $item->unit_price * $item->quantity;
                $lineTaxable = ($lineTotal / $subtotal) * $taxable;
                $rate = (float) ($item->product->hsnCode->gst_rate ?? $defaultGstRate);
                $gst += $lineTaxable * ($rate / 100);
            }
        }
        $gst = round($gst, 2);

        $shippingConfig = ShippingConfig::current();
        $shippingMethod = $shippingMethod ?: 'standard';
        $weightKg = ShippingCalculator::weightFor($items);

        if ($subtotal <= 0) {
            $shipping = ['charge' => 0.0, 'etaDays' => 0, 'available' => true];
        } else {
            $shipping = ShippingCalculator::resolve($shippingMethod, $state, $subtotal, $weightKg, $count, $shippingConfig);
        }
        $delivery = $shipping['charge'];

        $packaging = ($subtotal > 0 && $shippingConfig->packaging_apply_all) ? (float) $shippingConfig->packaging_fee : 0.0;
        $codFee = ($subtotal > 0 && $paymentMethod === 'cod' && $shippingConfig->cod_enabled) ? (float) $shippingConfig->cod_fee : 0.0;

        $total = $taxable + $gst + $delivery + $packaging + $codFee;

        return [
            'sub' => round($subtotal, 2),
            'disc' => round($discount, 2),
            'gst' => $gst,
            'deliv' => $delivery,
            'etaDays' => $shipping['etaDays'],
            'shippingAvailable' => $shipping['available'],
            'packaging' => round($packaging, 2),
            'codFee' => round($codFee, 2),
            'total' => round($total, 2),
            'count' => $count,
        ];
    }
}
