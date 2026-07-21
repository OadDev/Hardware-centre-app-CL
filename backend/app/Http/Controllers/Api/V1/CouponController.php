<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Support\CartCalculator;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = strtoupper(trim($data['code']));
        $coupon = Coupon::where('code', $code)->first();

        $items = $request->user()->cartItems()->with('product')->get();
        $subtotal = $items->sum(fn ($item) => (float) $item->product->price * $item->quantity);

        if (! $coupon || $coupon->status !== 'Active' || ($coupon->expires_at && $coupon->expires_at->isPast())) {
            return response()->json(['message' => 'Invalid coupon code'], 422);
        }

        if ($subtotal < (float) $coupon->min_order) {
            return response()->json(['message' => 'Minimum order value not met for this coupon'], 422);
        }

        $totals = CartCalculator::totals($items, $coupon);

        return response()->json([
            'message' => 'Coupon applied',
            'code' => $coupon->code,
            'totals' => $totals,
        ]);
    }
}
