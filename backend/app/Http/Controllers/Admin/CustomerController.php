<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->withCount('orders', 'wishlist');

        if ($search = $request->query('q')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $customers = $query->orderByDesc('id')->paginate($request->integer('per_page', 20));
        $customers->getCollection()->transform(fn (Customer $c) => $this->transform($c));

        // Store-wide counts (not affected by the filters above) for the
        // summary cards — only one page of customers is ever loaded at a
        // time, so these can't be derived client-side from what's on screen.
        $totalCustomers = Customer::count();
        $stats = [
            'total' => $totalCustomers,
            'active' => Customer::where('status', 'Active')->count(),
            'avgSpend' => $totalCustomers > 0 ? round((float) \App\Models\Order::sum('total') / $totalCustomers) : 0,
            'repeatCustomers' => \App\Models\Order::query()
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 3')
                ->get()
                ->count(),
        ];

        return response()->json(array_merge($customers->toArray(), ['stats' => $stats]));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);

        $customer->update($data);

        return response()->json($this->transform($customer->loadCount('orders', 'wishlist')));
    }

    private function transform(Customer $c): array
    {
        return [
            'id' => $c->customer_code,
            'name' => $c->name,
            'email' => $c->email,
            'city' => $c->city,
            'orders' => $c->orders_count,
            'totalSpend' => (float) $c->orders()->sum('total'),
            'wishlist' => $c->wishlist_count,
            'status' => $c->status,
        ];
    }
}
