<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::query()->with(['order', 'customer', 'seller']);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $returns = $query->orderByDesc('id')->paginate($request->integer('per_page', 20));
        $returns->getCollection()->transform(fn (ReturnRequest $r) => $this->transform($r));

        return response()->json($returns);
    }

    public function update(Request $request, ReturnRequest $return, NotificationService $notifications)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected'])],
        ]);

        $wasApproved = $return->status === 'Approved';
        $return->update($data);

        if ($data['status'] === 'Approved' && ! $wasApproved) {
            $return->loadMissing(['customer', 'order']);
            $notifications->notify('refund_processed', $return->customer->email, $return->customer->phone, [
                'customer_name' => $return->customer->name,
                'order_id' => $return->order->order_no,
            ], $return->customer->onesignal_player_id);
        }

        return response()->json($this->transform($return));
    }

    private function transform(ReturnRequest $r): array
    {
        return [
            'id' => $r->return_no,
            'orderId' => $r->order->order_no,
            'customerName' => $r->customer->name,
            'sellerName' => $r->seller->store_name,
            'amount' => (float) $r->amount,
            'reason' => $r->reason,
            'type' => $r->type,
            'status' => $r->status,
            'date' => $r->created_at->toIso8601String(),
        ];
    }
}
