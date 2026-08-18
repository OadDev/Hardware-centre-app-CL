<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $items = $seller->orderItems()
            ->with(['order.customer', 'order.address', 'product'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('seller.orders', ['seller' => $seller, 'items' => $items]);
    }
}
