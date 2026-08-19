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
    public static function totals(Collection $items, ?Coupon $coupon = null, ?string $paymentMethod = null): array
    {
        $subtotal = $items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $count = $items->sum('quantity');

        $discount = 0.0;
        if ($coupon && $subtotal >= (float) $coupon->min_order) {
            $discount = $coupon->discountFor($subtotal);
        }

        $taxable = $subtotal - $discount;
        $gstRate = (float) AppSetting::current()->default_gst_rate;
        $gst = round($taxable * ($gstRate / 100), 2);

        $shippingConfig = ShippingConfig::current();
        $freeShippingMin = (float) $shippingConfig->free_shipping_min;
        $delivery = ($subtotal >= $freeShippingMin || $subtotal <= 0) ? 0.0 : 99.0;

        $packaging = ($subtotal > 0 && $shippingConfig->packaging_apply_all) ? (float) $shippingConfig->packaging_fee : 0.0;
        $codFee = ($subtotal > 0 && $paymentMethod === 'cod' && $shippingConfig->cod_enabled) ? (float) $shippingConfig->cod_fee : 0.0;

        $total = $taxable + $gst + $delivery + $packaging + $codFee;

        return [
            'sub' => round($subtotal, 2),
            'disc' => round($discount, 2),
            'gst' => $gst,
            'deliv' => $delivery,
            'packaging' => round($packaging, 2),
            'codFee' => round($codFee, 2),
            'total' => round($total, 2),
            'count' => $count,
        ];
    }
}
