<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->whereHas('wishlistedBy', fn ($q) => $q->where('customers.id', $request->user()->id))
            ->with(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers'])
            ->get();

        return ProductResource::collection($products);
    }

    public function store(Request $request, Product $product)
    {
        $request->user()->wishlist()->firstOrCreate(['product_id' => $product->id]);

        return response()->json(['message' => 'Added to wishlist']);
    }

    public function destroy(Request $request, Product $product)
    {
        $request->user()->wishlist()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
