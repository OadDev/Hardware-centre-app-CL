<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Support\CartCalculator;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentController extends Controller
{
    public function createGatewayOrder(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'coupon_code' => ['nullable', 'string'],
        ]);

        $customer = $request->user();
        $items = $customer->cartItems()->with(['product.seller.categoryCommissions', 'product.category', 'product.priceTiers', 'productVariant.priceTiers'])->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 422);
        }

        $coupon = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->where('status', 'Active')->first();
        }

        $totals = CartCalculator::totals($items, $coupon);

        try {
            $orderId = $razorpay->createOrder($totals['total'], 'cart_'.$customer->id.'_'.Str::random(8));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'key_id' => $razorpay->keyId(),
            'gateway_order_id' => $orderId,
            'amount' => (int) round($totals['total'] * 100),
            'currency' => 'INR',
        ]);
    }
}
