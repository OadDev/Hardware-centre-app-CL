<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;

class SearchController extends Controller
{
    public function index()
    {
        return view('web.search', [
            'trending' => Product::query()->approved()->with(['brand', 'category'])->orderByDesc('sold_count')->limit(12)->get(),
            'brands' => Brand::where('status', 'Active')->orderBy('name')->withCount('products')->get(),
        ]);
    }
}
