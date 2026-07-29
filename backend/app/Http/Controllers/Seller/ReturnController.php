<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $returns = $seller->returns()->with(['order', 'orderItem.product'])->orderByDesc('created_at')->paginate(15);

        return view('seller.returns', ['seller' => $seller, 'returns' => $returns]);
    }
}
