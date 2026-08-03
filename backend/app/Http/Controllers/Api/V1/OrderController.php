<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\WarehouseInventory;
use App\Models\WarehouseStockMovement;
use App\Services\RazorpayService;
use App\Support\CartCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product.brand'])
            ->orderByDesc('placed_at')
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        $order->load(['items.product.brand']);

        return new OrderResource($order);
    }

    public function store(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer', Rule::exists('addresses', 'id')->where('customer_id', $request->user()->id)],
            'shipping_method' => ['required', Rule::in(['standard', 'express'])],
            'payment_method' => ['required', Rule::in(['upi', 'card', 'netbanking', 'cod'])],
            'coupon_code' => ['nullable', 'string'],
            'gateway_order_id' => ['required_unless:payment_method,cod', 'string'],
            'gateway_payment_id' => ['required_unless:payment_method,cod', 'string'],
            'gateway_signature' => ['required_unless:payment_method,cod', 'string'],
        ]);

        $customer = $request->user();

        // A payment can only ever pay for one order — if this payment id has
        // already been used (e.g. the client retried after a slow response),
        // return the order that was already created instead of charging again.
        if (! empty($data['gateway_payment_id'])) {
            $existing = Order::where('gateway_payment_id', $data['gateway_payment_id'])->first();
            if ($existing) {
                abort_unless($existing->customer_id === $customer->id, 409, 'This payment has already been used for another order.');
                $existing->load(['items.product.brand']);

                return new OrderResource($existing);
            }
        }

        if ($data['payment_method'] !== 'cod') {
            $verified = $razorpay->verifySignature($data['gateway_order_id'], $data['gateway_payment_id'], $data['gateway_signature']);
            abort_unless($verified, 422, 'Payment verification failed. Please try again.');
        }

        $items = $customer->cartItems()->with(['product.seller.categoryCommissions', 'product.category', 'productVariant'])->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 422);
        }

        $coupon = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->where('status', 'Active')->first();
        }

        $totals = CartCalculator::totals($items, $coupon);

        $order = Order::create([
            'order_no' => 'HC-'.now()->format('Y').'-'.str_pad((string) (Order::count() + 501), 5, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'address_id' => $data['address_id'],
            'coupon_id' => $coupon?->id,
            'subtotal' => $totals['sub'],
            'discount' => $totals['disc'],
            'gst_amount' => $totals['gst'],
            'delivery_charge' => $totals['deliv'],
            'total' => $totals['total'],
            'shipping_method' => $data['shipping_method'],
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_method'] === 'cod' ? 'Pending' : 'Paid',
            'gateway_provider' => $data['payment_method'] === 'cod' ? null : 'razorpay',
            'gateway_order_id' => $data['gateway_order_id'] ?? null,
            'gateway_payment_id' => $data['gateway_payment_id'] ?? null,
            'status' => 'Confirmed',
            'placed_at' => now(),
        ]);

        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->productVariant;
            $seller = $product->seller;
            $unitPrice = $item->unit_price;
            $lineTotal = $unitPrice * $item->quantity;
            $commissionPct = $seller->commissionRateFor($product->category);

            // FBA-style auto-fulfillment: if this seller has enough stock sitting
            // in an active warehouse, reserve it from there instead of relying on
            // the seller to ship it themselves.
            $warehouseInventory = WarehouseInventory::where('seller_id', $seller->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->where('quantity_available', '>=', $item->quantity)
                ->whereHas('warehouse', fn ($q) => $q->where('status', 'Active'))
                ->first();

            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'variant_label' => $variant?->label,
                'seller_id' => $seller->id,
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'commission_pct' => $commissionPct,
                'commission_amount' => round($lineTotal * $commissionPct / 100, 2),
                'fulfillment_type' => $warehouseInventory ? 'warehouse' : 'seller',
                'warehouse_id' => $warehouseInventory?->warehouse_id,
                'dispatch_status' => $warehouseInventory ? 'Pending' : null,
            ]);

            if ($warehouseInventory) {
                $warehouseInventory->decrement('quantity_available', $item->quantity);
                $warehouseInventory->increment('quantity_reserved', $item->quantity);

                WarehouseStockMovement::create([
                    'warehouse_id' => $warehouseInventory->warehouse_id,
                    'seller_id' => $seller->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'type' => 'order_reserved',
                    'quantity_delta' => -$item->quantity,
                    'reference_type' => 'order_item',
                    'reference_id' => $orderItem->id,
                    'note' => 'Reserved for order '.$order->order_no,
                ]);
            }

            $product->increment('sold_count', $item->quantity);

            if ($variant) {
                $variant->decrement('stock', min($item->quantity, $variant->stock));
            } else {
                $product->decrement('stock', min($item->quantity, $product->stock));
            }
        }

        if ($coupon) {
            $coupon->increment('used_count');
        }

        $customer->cartItems()->delete();

        $order->load(['items.product.brand']);

        return new OrderResource($order);
    }

    public function reorder(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        $order->load('items');
        foreach ($order->items as $item) {
            $cartItem = $request->user()->cartItems()->firstOrNew([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
            ]);
            $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + $item->quantity;
            $cartItem->save();
        }

        return response()->json(['message' => 'Items added to cart']);
    }
}
