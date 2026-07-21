<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenueVsCommission(Request $request)
    {
        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = (float) Order::whereNotIn('status', ['Cancelled'])
                ->whereYear('placed_at', $month->year)->whereMonth('placed_at', $month->month)
                ->sum('total');
            $commission = (float) \App\Models\OrderItem::whereHas('order', function ($q) use ($month) {
                $q->whereYear('placed_at', $month->year)->whereMonth('placed_at', $month->month);
            })->sum('commission_amount');

            $monthly[] = ['month' => $month->format('M'), 'revenue' => $revenue, 'commission' => $commission];
        }

        return response()->json($monthly);
    }

    public function topSellers()
    {
        $sellers = Seller::withSum('orderItems as revenue', 'line_total')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get(['id', 'store_name']);

        return response()->json($sellers->map(fn ($s) => [
            'store' => $s->store_name,
            'revenue' => (float) ($s->revenue ?? 0),
        ]));
    }
}
