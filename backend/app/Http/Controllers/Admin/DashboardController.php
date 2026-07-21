<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.console', [
            'user' => $request->user(),
        ]);
    }

    public function stats()
    {
        $totalRevenue = (float) Order::whereNotIn('status', ['Cancelled'])->sum('total');
        $todaysOrders = Order::whereDate('placed_at', today())->count();
        $pendingOrders = Order::whereIn('status', ['Pending', 'Confirmed', 'Packed'])->count();
        $activeSellers = Seller::where('account_status', 'Active')->count();
        $commission = (float) OrderItem::sum('commission_amount');
        $pendingSettlements = Settlement::where('status', 'Pending')->count();
        $pendingSettlementsAmount = (float) Settlement::where('status', 'Pending')->sum('amount');

        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthly[] = [
                'month' => $month->format('M'),
                'revenue' => (float) Order::whereNotIn('status', ['Cancelled'])
                    ->whereYear('placed_at', $month->year)->whereMonth('placed_at', $month->month)
                    ->sum('total'),
                'orders' => Order::whereYear('placed_at', $month->year)->whereMonth('placed_at', $month->month)->count(),
            ];
        }

        $activities = \App\Models\Activity::with('seller')->latest()->limit(10)->get()->map(fn ($a) => [
            'text' => $a->description,
            'time' => $a->created_at->diffForHumans(),
        ]);

        return response()->json([
            'total_revenue' => $totalRevenue,
            'todays_orders' => $todaysOrders,
            'pending_orders' => $pendingOrders,
            'active_sellers' => $activeSellers,
            'customers' => \App\Models\Customer::count(),
            'products' => \App\Models\Product::count(),
            'commission' => $commission,
            'pending_settlements' => $pendingSettlements,
            'pending_settlements_amount' => $pendingSettlementsAmount,
            'monthly_revenue' => $monthly,
            'activities' => $activities,
        ]);
    }
}
