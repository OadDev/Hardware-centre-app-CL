<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const STATUSES = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];

    public function index(Request $request)
    {
        $query = Order::query()->with(['customer']);

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
            'items' => $order->items->map(fn ($item) => [
                'product' => $item->product->name,
                'seller' => $item->seller->store_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ]),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'courier' => ['sometimes', 'nullable', 'string'],
            'awb' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($data['status'] === 'Delivered' && ! $order->delivered_at) {
            $data['delivered_at'] = now();
        }

        $order->update($data);

        return response()->json($this->transform($order));
    }

    private function transform(Order $o): array
    {
        return [
            'id' => $o->order_no,
            'date' => $o->placed_at->toIso8601String(),
            'customerName' => $o->customer->name,
            'items' => $o->items()->sum('quantity'),
            'amount' => (float) $o->total,
            'status' => $o->status,
            'payment' => $o->payment_status,
            'courier' => $o->courier,
            'awb' => $o->awb,
        ];
    }
}
