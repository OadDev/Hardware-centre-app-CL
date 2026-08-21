<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Support\AbandonedCartTracker;
use Illuminate\Http\Request;

class AbandonedCartController extends Controller
{
    public function index(Request $request)
    {
        AbandonedCartTracker::sweep();

        $query = AbandonedCart::query()->with(['customer', 'product', 'variant']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $carts = $query->orderByDesc('abandoned_at')->paginate($request->integer('per_page', 20));
        $carts->getCollection()->transform(fn (AbandonedCart $c) => $this->transform($c));

        return response()->json($carts);
    }

    private function transform(AbandonedCart $c): array
    {
        return [
            'id' => $c->id,
            'customerName' => $c->customer->name,
            'phone' => $c->customer->phone,
            'email' => $c->customer->email,
            'product' => $c->product->name,
            'variant' => $c->variant?->label,
            'quantity' => $c->quantity,
            'cartValue' => (float) $c->cart_value,
            'abandonedAt' => $c->abandoned_at->toIso8601String(),
            'purchasedAt' => $c->purchased_at?->toIso8601String(),
            'status' => $c->status,
        ];
    }
}
