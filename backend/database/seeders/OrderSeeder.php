<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::where('email', 'rajesh@kumarhardware.in')->first();
        if (! $customer) {
            return;
        }

        $address = $customer->addresses()->where('label', 'Shop')->first();

        $seedOrders = [
            ['no' => 'HC-2026-00519', 'status' => 'Out for Delivery', 'payment_status' => 'Paid', 'placed_at' => now()->subDays(2), 'items' => ['p1' => 1, 'p9' => 2]],
            ['no' => 'HC-2026-00481', 'status' => 'Delivered', 'payment_status' => 'Paid', 'placed_at' => now()->subDays(14), 'delivered_at' => now()->subDays(9), 'items' => ['p6' => 2, 'p4' => 3]],
            ['no' => 'HC-2026-00342', 'status' => 'Cancelled', 'payment_status' => 'Refunded', 'placed_at' => now()->subDays(58), 'items' => ['p2' => 1]],
            ['no' => 'HC-2026-00298', 'status' => 'Returned', 'payment_status' => 'Refunded', 'placed_at' => now()->subDays(72), 'items' => ['p8' => 1]],
        ];

        foreach ($seedOrders as $row) {
            $subtotal = 0;
            $lines = [];
            foreach ($row['items'] as $code => $qty) {
                $product = Product::where('product_code', $code)->first();
                if (! $product) {
                    continue;
                }
                $lineTotal = (float) $product->price * $qty;
                $subtotal += $lineTotal;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ];
            }

            $gst = round($subtotal * 0.18, 2);
            $delivery = $subtotal >= 5000 ? 0 : 99;
            $total = $subtotal + $gst + $delivery;

            $order = Order::updateOrCreate(
                ['order_no' => $row['no']],
                [
                    'customer_id' => $customer->id,
                    'address_id' => $address?->id,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'gst_amount' => $gst,
                    'delivery_charge' => $delivery,
                    'total' => $total,
                    'shipping_method' => 'standard',
                    'payment_method' => 'upi',
                    'payment_status' => $row['payment_status'],
                    'status' => $row['status'],
                    'placed_at' => $row['placed_at'],
                    'delivered_at' => $row['delivered_at'] ?? null,
                ]
            );

            $order->items()->delete();
            foreach ($lines as $line) {
                $seller = $line['product']->seller;
                $commissionPct = $seller->commission_pct ?? 10;
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'seller_id' => $line['product']->seller_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                    'commission_pct' => $commissionPct,
                    'commission_amount' => round($line['line_total'] * $commissionPct / 100, 2),
                ]);
            }
        }
    }
}
