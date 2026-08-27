<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Settlement;
use App\Models\WarehouseInventory;
use App\Models\WarehouseStockMovement;
use App\Services\NotificationService;
use App\Services\RazorpayService;
use App\Support\AbandonedCartTracker;
use App\Support\CartCalculator;
use App\Support\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

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

    public function store(Request $request, RazorpayService $razorpay, NotificationService $notifications)
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

        $address = Address::find($data['address_id']);

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

        $items = $customer->cartItems()->with(['product.seller.categoryCommissions', 'product.category', 'product.priceTiers', 'productVariant.priceTiers'])->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 422);
        }

        $coupon = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->where('status', 'Active')->first();
        }

        $subtotalPreview = $items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $weightKg = ShippingCalculator::weightFor($items);
        $shipping = ShippingCalculator::resolve($data['shipping_method'], $address?->state, $subtotalPreview, $weightKg, $items->sum('quantity'));
        abort_unless($shipping['available'], 422, ucfirst($data['shipping_method']).' delivery is not available for this address.');

        $totals = CartCalculator::totals($items, $coupon, $data['payment_method'], $data['shipping_method'], $address?->state);

        $order = Order::create([
            'order_no' => 'HC-'.now()->format('Y').'-'.str_pad((string) (Order::count() + 501), 5, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'address_id' => $data['address_id'],
            'coupon_id' => $coupon?->id,
            'subtotal' => $totals['sub'],
            'discount' => $totals['disc'],
            'gst_amount' => $totals['gst'],
            'delivery_charge' => $totals['deliv'] + $totals['packaging'] + $totals['codFee'],
            'total' => $totals['total'],
            'shipping_method' => $data['shipping_method'],
            'eta_days' => $totals['etaDays'],
            'gst_number' => $address?->gst_number,
            'customer_email' => $address?->email ?: $customer->email,
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

            AbandonedCartTracker::markPurchased($customer->id, $product->id, $variant?->id);

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

        if ($data['payment_method'] !== 'cod') {
            $this->settleSellers($order, $razorpay);
        }

        $customer->cartItems()->delete();

        $order->load(['items.product.brand']);

        $notifications->notify('order_confirmation', $order->customer_email ?: $customer->email, $customer->phone, [
            'customer_name' => $customer->name,
            'order_id' => $order->order_no,
            'tracking_link' => config('app.url').'/track-order',
        ], $customer->onesignal_player_id);

        return new OrderResource($order);
    }

    /**
     * Splits the already-captured payment across the sellers in this order
     * via Razorpay Route where their linked account is active, and falls
     * back to a "Pending" manual-transfer settlement everywhere else (a
     * seller not yet linked, or whose Route transfer failed). This must
     * never throw back into the caller — the customer has already paid and
     * the order has to succeed regardless of how settlement plumbing goes;
     * any failure here just means the admin settles that seller by hand.
     */
    private function settleSellers(Order $order, RazorpayService $razorpay): void
    {
        try {
            $order->loadMissing('items.seller');
            $bySeller = $order->items->groupBy('seller_id');

            $routeTransfers = [];
            $routeSellers = [];
            foreach ($bySeller as $items) {
                $seller = $items->first()->seller;
                $net = round($items->sum(fn ($i) => $i->line_total - $i->commission_amount), 2);

                if ($seller->razorpay_account_id && $seller->razorpay_account_status === 'Activated') {
                    $routeTransfers[] = ['account' => $seller->razorpay_account_id, 'amount' => $net, 'notes' => ['order' => $order->order_no, 'seller_code' => $seller->seller_code]];
                    $routeSellers[] = $seller;
                } else {
                    $this->recordSettlement($order, $seller, $net, 'Pending', 'Manual Bank Transfer');
                }
            }

            if (empty($routeTransfers) || ! $order->gateway_payment_id) {
                return;
            }

            $results = $razorpay->transfer($order->gateway_payment_id, $routeTransfers);
            foreach ($results as $i => $result) {
                $seller = $routeSellers[$i];
                $amount = $routeTransfers[$i]['amount'];

                $this->recordSettlement(
                    $order,
                    $seller,
                    $amount,
                    $result['ok'] ? 'Paid' : 'Pending',
                    $result['ok'] ? 'Razorpay Route' : 'Manual Bank Transfer',
                    $result['transfer_id'] ?? null,
                );
            }
        } catch (Throwable $e) {
            Log::error('Seller settlement failed for order '.$order->order_no.': '.$e->getMessage());
        }
    }

    private function recordSettlement(Order $order, $seller, float $amount, string $status, string $mode, ?string $transferId = null): void
    {
        Settlement::create([
            'settlement_no' => 'STL-'.strtoupper(Str::random(10)),
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'amount' => $amount,
            'status' => $status,
            'mode' => $mode,
            'razorpay_transfer_id' => $transferId,
            'settled_at' => $status === 'Paid' ? now() : null,
        ]);
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
