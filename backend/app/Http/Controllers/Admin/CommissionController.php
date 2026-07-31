<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CommissionTier;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\SellerCategoryCommission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $sellers = Seller::orderByDesc('id')->paginate($request->integer('per_page', 20));

        $sellers->getCollection()->transform(fn (Seller $s) => [
            'id' => $s->seller_code,
            'store' => $s->store_name,
            'revenue' => (float) $s->orderItems()->sum('line_total'),
            'commissionPct' => (float) $s->commission_pct,
            'marketplaceEarning' => (float) $s->orderItems()->sum('commission_amount'),
        ]);

        $totalEarnings = (float) OrderItem::sum('commission_amount');
        $avgRate = (float) Seller::avg('commission_pct');

        return response()->json([
            'sellers' => $sellers,
            'total_earnings' => $totalEarnings,
            'avg_commission_rate' => round($avgRate, 1),
            'tiers' => CommissionTier::orderBy('sort_order')->get(),
        ]);
    }

    public function updateSeller(Request $request, Seller $seller)
    {
        $data = $request->validate([
            'commission_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $seller->update($data);

        return response()->json(['message' => 'Commission rate updated']);
    }

    public function updateTier(Request $request, CommissionTier $tier)
    {
        $data = $request->validate([
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $tier->update($data);

        return response()->json($tier);
    }

    public function categoryOverrides(Seller $seller)
    {
        return response()->json(
            $seller->categoryCommissions()->with('category')->get()->map(fn (SellerCategoryCommission $o) => [
                'categoryId' => $o->category->category_code,
                'commissionPct' => (float) $o->commission_pct,
            ])
        );
    }

    public function setCategoryOverride(Request $request, Seller $seller)
    {
        $data = $request->validate([
            'category_code' => ['required', 'string', 'exists:categories,category_code'],
            'commission_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $categoryId = Category::where('category_code', $data['category_code'])->value('id');

        SellerCategoryCommission::updateOrCreate(
            ['seller_id' => $seller->id, 'category_id' => $categoryId],
            ['commission_pct' => $data['commission_pct']]
        );

        return $this->categoryOverrides($seller);
    }

    public function removeCategoryOverride(Seller $seller, Category $category)
    {
        $seller->categoryCommissions()->where('category_id', $category->id)->delete();

        return $this->categoryOverrides($seller);
    }
}
