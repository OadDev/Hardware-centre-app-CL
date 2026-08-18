<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Seller;

class HomeController extends Controller
{
    public function index()
    {
        $base = Product::query()->approved()->with(['brand', 'category', 'images']);

        return view('web.home', [
            'banners' => Banner::where('status', 'Active')->orderBy('sort_order')->get(),
            'popular' => (clone $base)->orderByDesc('reviews_count')->limit(6)->get(),
            'deals' => (clone $base)->where('tag', 'Deal')->orderByDesc('rating')->limit(4)->get(),
            'trending' => (clone $base)->orderByDesc('sold_count')->limit(8)->get(),
            'suppliers' => Seller::where('account_status', 'Active')->orderByDesc('rating')->limit(3)->get(),
            'brands' => Brand::where('status', 'Active')->orderByDesc('featured')->orderBy('name')->limit(12)->get(),
        ]);
    }
}
