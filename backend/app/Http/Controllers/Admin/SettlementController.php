<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = Settlement::query()->with('seller');

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

    public function retry(Settlement $settlement)
    {
        abort_unless($settlement->status === 'Failed', 422, 'Only failed settlements can be retried');

        $settlement->update([
            'status' => 'Paid',
            'utr' => 'UTR'.random_int(100000000000, 999999999999),
            'settled_at' => now(),
        ]);

        return response()->json($this->transform($settlement));
    }

    private function transform(Settlement $s): array
    {
        return [
            'id' => $s->settlement_no,
            'sellerName' => $s->seller->store_name,
            'amount' => (float) $s->amount,
            'mode' => $s->mode,
            'date' => $s->created_at->toIso8601String(),
            'utr' => $s->utr,
            'status' => $s->status,
        ];
    }
}
