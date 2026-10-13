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
        $base = Product::query()->approved()->with(['brand', 'category', 'seller', 'specs', 'images', 'variants.priceTiers']);
        $featuredBrands = Brand::where('status', 'Active')->orderByDesc('featured')->orderBy('name')->limit(8)->get();

        return response()->json([
            'banners' => BannerResource::collection(Banner::where('status', 'Active')->orderBy('sort_order')->get()),
            'categories' => CategoryResource::collection(Category::topLevel()->where('status', 'Active')->orderBy('sort_order')->limit(7)->get()),
            'popular' => ProductResource::collection((clone $base)->orderByDesc('reviews_count')->limit(4)->get()),
            'deals' => ProductResource::collection((clone $base)->where('tag', 'Deal')->orderByDesc('rating')->limit(4)->get()),
            'trending' => ProductResource::collection((clone $base)->orderByDesc('sold_count')->limit(4)->get()),
            'recommended' => ProductResource::collection((clone $base)->orderByDesc('rating')->limit(4)->get()),
            'suppliers' => SellerResource::collection(Seller::where('account_status', 'Active')->orderByDesc('rating')->limit(3)->get()),
            // 'brands' stays a plain string list — it's what every app build
            // already installed on a real device expects (anything else
            // crashes HomeScreen's initials() call, which assumes a string).
            // 'featured_brands' is the richer shape (logo_url, tappable)
            // added for an app build that hasn't shipped to a device yet.
            'brands' => $featuredBrands->pluck('name'),
            'featured_brands' => $featuredBrands->map(fn (Brand $b) => [
                'id' => $b->brand_code,
                'name' => $b->name,
                'logo_url' => $b->logo_url,
            ]),
        ]);
    }
}
