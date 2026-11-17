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

        $categories = Category::topLevel()->where('status', 'Active')->orderBy('sort_order')->limit(7)
            ->withCount(['products' => fn ($q) => $q->approved()])
            ->with(['children' => fn ($q) => $q->where('status', 'Active')->withCount(['products' => fn ($q) => $q->approved()])])
            ->get();
        // A top-level category's own count only covers products assigned
        // directly to it; real products live under its subcategories, so
        // fold those counts in too (see CategoriesController for the full
        // writeup of why this is needed).
        $categories->each(fn (Category $cat) => $cat->products_count += $cat->children->sum('products_count'));

        // Popular/Trending are admin-curated via is_popular/is_trending on
        // the product (Admin > Product Management) — only falling back to
        // the old auto-ranked order if the admin hasn't picked anything yet,
        // so the homepage is never empty.
        $popular = (clone $base)->where('is_popular', true)->limit(8)->get();
        if ($popular->isEmpty()) {
            $popular = (clone $base)->orderByDesc('reviews_count')->limit(4)->get();
        }
        $trending = (clone $base)->where('is_trending', true)->limit(8)->get();
        if ($trending->isEmpty()) {
            $trending = (clone $base)->orderByDesc('sold_count')->limit(4)->get();
        }

        return response()->json([
            'banners' => BannerResource::collection(Banner::where('status', 'Active')->orderBy('sort_order')->get()),
            'categories' => CategoryResource::collection($categories),
            'popular' => ProductResource::collection($popular),
            'deals' => ProductResource::collection((clone $base)->where('tag', 'Deal')->orderByDesc('rating')->limit(4)->get()),
            'trending' => ProductResource::collection($trending),
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
