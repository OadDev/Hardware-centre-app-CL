<?php

namespace App\Support;

use App\Models\Order;

class OrderTimeline
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

    public static function build(Order $order): array
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
