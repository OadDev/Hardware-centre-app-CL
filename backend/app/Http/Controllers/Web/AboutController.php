<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;

class AboutController extends Controller
{
    public function index()
    {
        return view('web.about', [
            'supplierCount' => Seller::where('account_status', 'Active')->count(),
            'productCount' => Product::query()->approved()->count(),
        ]);
    }
}
