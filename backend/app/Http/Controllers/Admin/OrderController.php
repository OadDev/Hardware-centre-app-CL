<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseInventory;
use App\Models\WarehouseStockMovement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const STATUSES = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];

    private const STATUS_TEMPLATE = [
        'Shipped' => 'order_shipped',
        'Delivered' => 'order_delivered',
        'Cancelled' => 'order_cancelled',
    ];

    public function index(Request $request)
    {
        $query = Order::query()->with(['customer', 'address']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($payment = $request->query('payment')) {
            $query->where('payment_status', $payment);
        }

        $orders = $query->orderByDesc('placed_at')->paginate($request->integer('per_page', 20));
        $orders->getCollection()->transform(fn (Order $o) => $this->transform($o));

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'address', 'items.product', 'items.seller']);

        return response()->json([
            ...$this->transform($order),
            'customerPhone' => $order->customer->phone,
            'address' => $order->address ? [
                'contactName' => $order->address->contact_name,
                'line' => $order->address->line,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'pincode' => $order->address->pincode,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'product' => $item->product->name,
                'seller' => $item->seller->store_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ]),
        ]);
    }

    public function update(Request $request, Order $order, NotificationService $notifications)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'courier' => ['sometimes', 'nullable', 'string'],
            'awb' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($data['status'] === 'Delivered' && ! $order->delivered_at) {
            $data['delivered_at'] = now();
        }

        $previousStatus = $order->status;
        $order->update($data);

        if ($data['status'] === 'Cancelled' && $previousStatus !== 'Cancelled') {
            $this->releaseWarehouseReservations($order);
        }

        if ($data['status'] !== $previousStatus && isset(self::STATUS_TEMPLATE[$data['status']])) {
            $order->loadMissing('customer');
            $notifications->notify(self::STATUS_TEMPLATE[$data['status']], $order->customer->email, $order->customer->phone, [
                'customer_name' => $order->customer->name,
                'order_id' => $order->order_no,
                'tracking_link' => config('app.url').'/track-order',
            ], $order->customer->onesignal_player_id);
        }

        return response()->json($this->transform($order));
    }

    /**
     * Undo any warehouse stock reservation for items that haven't shipped
     * yet, so cancelled orders don't permanently lock up warehouse stock.
     */
    private function releaseWarehouseReservations(Order $order): void
    {
        $order->load('items');

        foreach ($order->items as $item) {
            if ($item->fulfillment_type !== 'warehouse' || ! in_array($item->dispatch_status, ['Pending', 'Packed'], true)) {
                continue;
            }

            $inventory = WarehouseInventory::where('warehouse_id', $item->warehouse_id)
                ->where('seller_id', $item->seller_id)
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($inventory) {
                $inventory->increment('quantity_available', $item->quantity);
                $inventory->decrement('quantity_reserved', min($item->quantity, $inventory->quantity_reserved));

                WarehouseStockMovement::create([
                    'warehouse_id' => $item->warehouse_id,
                    'seller_id' => $item->seller_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'type' => 'order_cancelled_release',
                    'quantity_delta' => $item->quantity,
                    'reference_type' => 'order_item',
                    'reference_id' => $item->id,
                    'note' => 'Released — order '.$order->order_no.' cancelled',
                ]);
            }

            $item->update(['dispatch_status' => 'Cancelled']);
        }
    }

    private function transform(Order $o): array
    {
        $o->loadMissing(['customer', 'address']);

        return [
            'id' => $o->order_no,
            'date' => $o->placed_at->toIso8601String(),
            // The account name is often just an auto-generated "Customer 1234"
            // placeholder from OTP signup — the name typed on the delivery
            // address at checkout is the real one, so prefer that.
            'customerName' => $o->address?->contact_name ?: $o->customer->name,
            'items' => $o->items()->sum('quantity'),
            'amount' => (float) $o->total,
            'status' => $o->status,
            'payment' => $o->payment_status,
            'courier' => $o->courier,
            'awb' => $o->awb,
        ];
    }
}
