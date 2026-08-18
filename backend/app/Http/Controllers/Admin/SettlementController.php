<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = Settlement::query()->with(['seller', 'order']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($mode = $request->query('mode')) {
            $query->where('mode', $mode);
        }

        $settlements = $query->orderByDesc('id')->paginate($request->integer('per_page', 20));
        $settlements->getCollection()->transform(fn (Settlement $s) => $this->transform($s));

        return response()->json($settlements);
    }

    /**
     * Records that the admin has actually transferred this seller's payout
     * — by NEFT/IMPS for a Manual Bank Transfer settlement, or by manually
     * confirming a Razorpay Route transfer that Razorpay's side reports as
     * settled. The UTR/reference number is whatever the admin's own bank
     * gave them; we don't generate or verify it ourselves.
     */
    public function markPaid(Request $request, Settlement $settlement, NotificationService $notifications)
    {
        abort_if($settlement->status === 'Paid', 422, 'This settlement is already marked as paid.');

        $data = $request->validate([
            'utr' => ['required', 'string', 'max:60'],
        ]);

        $settlement->update([
            'status' => 'Paid',
            'utr' => $data['utr'],
            'settled_at' => now(),
        ]);

        $settlement->loadMissing('seller');
        $notifications->notify('settlement_paid', $settlement->seller->email, $settlement->seller->phone, [
            'customer_name' => $settlement->seller->owner_name,
        ]);

        return response()->json($this->transform($settlement));
    }

    private function transform(Settlement $s): array
    {
        return [
            'id' => $s->settlement_no,
            'sellerName' => $s->seller->store_name,
            'orderId' => $s->order?->order_no,
            'amount' => (float) $s->amount,
            'mode' => $s->mode,
            'date' => $s->created_at->toIso8601String(),
            'utr' => $s->utr,
            'razorpayTransferId' => $s->razorpay_transfer_id,
            'status' => $s->status,
        ];
    }
}
