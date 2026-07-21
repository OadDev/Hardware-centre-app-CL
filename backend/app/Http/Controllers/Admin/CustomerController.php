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

        return response()->json($customers);
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
