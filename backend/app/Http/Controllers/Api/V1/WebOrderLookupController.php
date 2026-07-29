<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class WebOrderLookupController extends Controller
{
    // Maps raw order status -> index of the display step (below) it has reached.
    private const STATUS_STEP = [
        'Pending' => -1,
        'Confirmed' => 0,
        'Packed' => 0,
        'Shipped' => 1,
        'Out for Delivery' => 2,
        'Delivered' => 3,
    ];

    public function track(Request $request)
    {
        $data = $request->validate([
            'order_no' => ['required', 'string'],
            'contact' => ['required', 'string'],
        ]);

        $order = Order::where('order_no', trim($data['order_no']))
            ->with(['items.product.brand', 'customer'])
            ->first();

        if (! $order || ! $this->contactMatches($order, $data['contact'])) {
            return response()->json(['message' => 'We couldn\'t find an order with those details.'], 404);
        }

        return response()->json([
            'order' => $this->summarize($order),
            'timeline' => $this->timeline($order),
        ]);
    }

    public function myOrders(Request $request)
    {
        $data = $request->validate([
            'contact' => ['required', 'string'],
        ]);

        $contact = trim($data['contact']);
        $customer = Customer::where('email', $contact)->orWhere('phone', $contact)->first();

        if (! $customer) {
            return response()->json(['orders' => []]);
        }

        $orders = $customer->orders()
            ->with(['items.product.brand'])
            ->orderByDesc('placed_at')
            ->get()
            ->map(fn (Order $order) => $this->summarize($order));

        return response()->json(['orders' => $orders]);
    }

    private function contactMatches(Order $order, string $contact): bool
    {
        $contact = trim($contact);
        $customer = $order->customer;

        return $customer && ($customer->email === $contact || $customer->phone === $contact);
    }

    private function summarize(Order $order): array
    {
        $items = $order->items;
        $count = $items->sum('quantity');
        $first = $items->first();

        return [
            'no' => $order->order_no,
            'status' => $order->status,
            'date' => $order->placed_at->format('d M Y'),
            'total' => (float) $order->total,
            'item_count' => $count,
            'title' => $first
                ? $first->product->name.($count > 1 ? ' + '.($count - 1).' more' : '')
                : 'Order',
            'seller' => $first?->product?->seller?->store_name,
        ];
    }

    private function timeline(Order $order): array
    {
        if (in_array($order->status, ['Cancelled', 'Returned'])) {
            return [
                ['title' => 'Order Placed', 'time' => $order->placed_at->format('d M Y, g:i A'), 'state' => 'done'],
                ['title' => $order->status, 'time' => $order->updated_at->format('d M Y, g:i A'), 'state' => 'current'],
            ];
        }

        $currentStep = self::STATUS_STEP[$order->status] ?? -1;

        $displaySteps = [
            ['title' => 'Order Confirmed', 'time' => $order->placed_at->copy()->addHours(2)->format('d M Y, g:i A')],
            ['title' => 'Shipped', 'time' => $order->placed_at->copy()->addDay()->format('d M Y, g:i A')],
            ['title' => 'Out for Delivery', 'time' => $order->placed_at->copy()->addDays(4)->format('d M Y, g:i A')],
            ['title' => 'Delivered', 'time' => $order->delivered_at?->format('d M Y, g:i A') ?? 'Expected by '.$order->placed_at->copy()->addDays(5)->format('d M Y')],
        ];

        $steps = [['title' => 'Order Placed', 'time' => $order->placed_at->format('d M Y, g:i A'), 'state' => 'done']];

        foreach ($displaySteps as $i => $step) {
            if ($i < $currentStep) {
                $step['state'] = 'done';
            } elseif ($i === $currentStep) {
                $step['state'] = 'current';
            } else {
                $step['state'] = 'pending';
            }
            $steps[] = $step;
        }

        return $steps;
    }
}
