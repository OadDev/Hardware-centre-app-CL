<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'warehouse_address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'category_interest' => ['nullable', 'string', 'max:255'],
            'commission_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $user = User::create([
            'name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('Seller');

        $seller = Seller::create([
            'seller_code' => 'SLR-'.(1000 + Seller::count()),
            'user_id' => $user->id,
            'store_name' => $data['store_name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'state' => $data['state'],
            'warehouse_address' => $data['warehouse_address'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'category_interest' => $data['category_interest'] ?? null,
            'commission_pct' => $data['commission_pct'] ?? 10,
            'approval_status' => 'Approved',
            'account_status' => 'Active',
            'kyc_status' => 'Verified',
            'joined_at' => now(),
        ]);

        return response()->json($this->transform($seller->loadCount('products')), 201);
    }

    public function show(Seller $seller)
    {
        return response()->json($this->transform($seller->loadCount('products')));
    }

    public function update(Request $request, Seller $seller, NotificationService $notifications)
    {
        $data = $request->validate([
            'approval_status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected'])],
            'account_status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'kyc_status' => ['sometimes', Rule::in(['Verified', 'Under Review', 'Rejected'])],
            'commission_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $wasApproved = $seller->approval_status === 'Approved';
        $seller->update($data);

        if (($data['approval_status'] ?? null) === 'Approved' && ! $wasApproved) {
            $notifications->notify('seller_kyc_approved', $seller->email, $seller->phone, [
                'customer_name' => $seller->owner_name,
            ]);
        }

        return response()->json($this->transform($seller->loadCount('products')));
    }

    /**
     * Creates (or re-attempts) the seller's Razorpay Route linked account
     * using whatever PAN/bank details they've saved on their side. Success
     * here only means the account and stakeholder were accepted by
     * Razorpay — actual payout readiness ("Activated") depends on
     * Razorpay's own KYC review, which happens on their side afterward.
     */
    public function linkRazorpay(Seller $seller, RazorpayService $razorpay)
    {
        $result = $razorpay->createLinkedAccount($seller);

        $seller->update([
            'razorpay_account_id' => $result['account_id'] ?? $seller->razorpay_account_id,
            'razorpay_account_status' => $result['ok'] ? ($result['status'] ?? 'Pending') : 'Not Linked',
            'razorpay_account_error' => $result['error'] ?? null,
        ]);

        if (! $result['ok']) {
            return response()->json(['message' => $result['error'] ?? 'Could not link this seller to Razorpay Route.'], 422);
        }

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
            'pan' => $s->pan_number,
            'bankAccountHolder' => $s->bank_account_holder,
            'bankAccountMasked' => $s->masked_bank_account_number,
            'bankIfsc' => $s->bank_ifsc,
            'hasPayoutDetails' => $s->hasPayoutDetails(),
            'razorpayAccountId' => $s->razorpay_account_id,
            'razorpayAccountStatus' => $s->razorpay_account_status,
            'razorpayAccountError' => $s->razorpay_account_error,
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
