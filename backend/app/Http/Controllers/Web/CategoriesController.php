<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', 'Active')
            ->orderBy('sort_order')
            ->withCount('products')
            ->with(['products' => function ($q) {
                $q->approved()->with(['brand', 'category'])->orderByDesc('reviews_count')->limit(4);
            }])
            ->get();

        return view('web.categories', ['categories' => $categories]);
    }
}
