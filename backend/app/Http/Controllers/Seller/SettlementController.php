<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $settlements = $seller->settlements()->orderByDesc('created_at')->paginate(15);

        return view('seller.settlements', ['seller' => $seller, 'settlements' => $settlements]);
    }
}
