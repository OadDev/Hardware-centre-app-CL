<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->where('status', 'Active')->orderBy('sort_order')
            ->withCount(['products' => fn ($q) => $q->approved()])
            ->get();

        // A top-level category's own products_count only counts rows
        // assigned directly to it, but products actually live under its
        // subcategories — fold each category's children's counts into its
        // own so "Electrical" doesn't show 0 while its subcategories have
        // all the real products.
        $categories->each(function (Category $cat) use ($categories) {
            $children = $categories->where('parent_id', $cat->id);
            $cat->products_count += $children->sum('products_count');
        });

        return CategoryResource::collection($categories);
    }

    public function products(Request $request, Category $category)
    {
        $category->load('parent');
        $sort = $request->query('sort', 'pop');

        $query = Product::query()->approved()
            ->with(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers'])
            ->whereIn('category_id', $category->selfAndDescendantIds());

        match ($sort) {
            'low' => $query->orderBy('price'),
            'high' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('reviews_count'),
        };

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => ProductResource::collection($query->paginate($request->integer('per_page', 20))),
        ]);
    }
}
