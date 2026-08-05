<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\IndianStates;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('web.checkout', ['indianStates' => IndianStates::ALL]);
    }

    public function confirmation(string $order_no)
    {
        return view('web.order-confirmation', ['orderNo' => $order_no]);
    }
}
