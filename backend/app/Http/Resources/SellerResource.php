<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Seller $this */
        return [
            'id' => $this->seller_code,
            'name' => $this->store_name,
            'owner_name' => $this->owner_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'loc' => $this->city,
            'state' => $this->state,
            'gst' => $this->gst_number,
            'commission_pct' => (float) $this->commission_pct,
            'wallet_balance' => (float) $this->wallet_balance,
            'rating' => (float) $this->rating,
            'years' => $this->joined_at ? (int) now()->diffInYears($this->joined_at, true) : 0,
            'approval_status' => $this->approval_status,
            'account_status' => $this->account_status,
            'kyc_status' => $this->kyc_status,
            'products_count' => $this->products_count ?? $this->products()->count(),
            'revenue' => (float) $this->orderItems()->sum('line_total'),
        ];
    }
}
