<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function index()
    {
        return view('web.orders');
    }
}
