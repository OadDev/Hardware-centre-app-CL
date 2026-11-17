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

        // withCount('products') only counts rows whose category_id is this
        // top-level category's own id, but products are actually assigned
        // to its subcategories — so a top-level count needs its children's
        // counts folded in too, or categories with subcategories always
        // show 0 even when full of products.
        $categories->each(fn (Category $cat) => $cat->products_count += $cat->children->sum('products_count'));

        return view('web.categories', ['categories' => $categories]);
    }
}
