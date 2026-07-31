<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::topLevel()->where('status', 'Active')
            ->orderBy('sort_order')
            ->withCount('products')
            ->with([
                'children' => fn ($q) => $q->where('status', 'Active')->withCount('products'),
                'products' => function ($q) {
                    $q->approved()->with(['brand', 'category'])->orderByDesc('reviews_count')->limit(4);
                },
            ])
            ->get();

        return view('web.categories', ['categories' => $categories]);
    }
}
