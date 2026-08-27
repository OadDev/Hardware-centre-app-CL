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
            ->withCount(['products' => fn ($q) => $q->approved()])
            ->with([
                'children' => fn ($q) => $q->where('status', 'Active')
                    ->withCount(['products' => fn ($q) => $q->approved()]),
                'products' => function ($q) {
                    $q->approved()->with(['brand', 'category', 'images'])->orderByDesc('reviews_count')->limit(4);
                },
            ])
            ->get();

        return view('web.categories', ['categories' => $categories]);
    }
}
