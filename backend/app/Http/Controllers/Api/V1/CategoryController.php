<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\CategoryResource::collection(
            Category::where('status', 'Active')->orderBy('sort_order')->get()
        );
    }

    public function products(Request $request, Category $category)
    {
        $sort = $request->query('sort', 'pop');

        $query = Product::query()->approved()
            ->with(['brand', 'category', 'seller', 'specs'])
            ->where('category_id', $category->id);

        match ($sort) {
            'low' => $query->orderBy('price'),
            'high' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('reviews_count'),
        };

        return response()->json([
            'category' => new \App\Http\Resources\CategoryResource($category),
            'products' => ProductResource::collection($query->paginate($request->integer('per_page', 20))),
        ]);
    }
}
