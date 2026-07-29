<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $revenue = $seller->orderItems()->sum('line_total');
        $productsCount = $seller->products()->count();
        $pendingOrders = $seller->orderItems()
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['Delivered', 'Cancelled', 'Returned']))
            ->distinct('order_id')
            ->count('order_id');
        $pendingSettlements = $seller->settlements()->where('status', 'Pending')->count();

        $recentItems = $seller->orderItems()
            ->with(['order', 'product'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('seller.dashboard', [
            'seller' => $seller,
            'revenue' => $revenue,
            'productsCount' => $productsCount,
            'pendingOrders' => $pendingOrders,
            'pendingSettlements' => $pendingSettlements,
            'recentItems' => $recentItems,
        ]);
    }
}
