<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SellerResource;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $base = Product::query()->approved()->with(['brand', 'category', 'seller', 'specs', 'images', 'variants']);

        return response()->json([
            'banners' => BannerResource::collection(Banner::where('status', 'Active')->orderBy('sort_order')->get()),
            'categories' => CategoryResource::collection(Category::where('status', 'Active')->orderBy('sort_order')->limit(7)->get()),
            'popular' => ProductResource::collection((clone $base)->orderByDesc('reviews_count')->limit(4)->get()),
            'deals' => ProductResource::collection((clone $base)->where('tag', 'Deal')->orderByDesc('rating')->limit(4)->get()),
            'trending' => ProductResource::collection((clone $base)->orderByDesc('sold_count')->limit(4)->get()),
            'recommended' => ProductResource::collection((clone $base)->orderByDesc('rating')->limit(4)->get()),
            'suppliers' => SellerResource::collection(Seller::where('account_status', 'Active')->orderByDesc('rating')->limit(3)->get()),
            'brands' => Brand::where('status', 'Active')->orderByDesc('featured')->orderBy('name')->limit(8)->pluck('name'),
        ]);
    }
}
