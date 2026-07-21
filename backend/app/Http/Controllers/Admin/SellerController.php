<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::query()->withCount('products');

        if ($search = $request->query('q')) {
            $query->where('store_name', 'like', "%{$search}%");
        }
        if ($approval = $request->query('approval')) {
            $query->where('approval_status', $approval);
        }
        if ($status = $request->query('status')) {
            $query->where('account_status', $status);
        }

        $sellers = $query->orderByDesc('id')->paginate($request->integer('per_page', 20));
        $sellers->getCollection()->transform(fn (Seller $s) => $this->transform($s));

        return response()->json($sellers);
    }

    public function show(Seller $seller)
    {
        return response()->json($this->transform($seller->loadCount('products')));
    }

    public function update(Request $request, Seller $seller)
    {
        $data = $request->validate([
            'approval_status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected'])],
            'account_status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'kyc_status' => ['sometimes', Rule::in(['Verified', 'Under Review', 'Rejected'])],
            'commission_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $seller->update($data);

        return response()->json($this->transform($seller->loadCount('products')));
    }

    private function transform(Seller $s): array
    {
        return [
            'id' => $s->seller_code,
            'store' => $s->store_name,
            'name' => $s->owner_name,
            'email' => $s->email,
            'phone' => $s->phone,
            'city' => $s->city,
            'state' => $s->state,
            'gst' => $s->gst_number,
            'products' => $s->products_count,
            'revenue' => (float) $s->orderItems()->sum('line_total'),
            'commissionPct' => (float) $s->commission_pct,
            'walletBalance' => (float) $s->wallet_balance,
            'rating' => (float) $s->rating,
            'approval' => $s->approval_status,
            'status' => $s->account_status,
            'kyc' => $s->kyc_status,
            'joined' => optional($s->joined_at)->toIso8601String(),
        ];
    }
}
