<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Customer $this */
        return [
            'id' => $this->customer_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'business_name' => $this->business_name,
            'gst' => $this->gst_number,
            'city' => $this->city,
            'state' => $this->state,
            'wallet_balance' => (float) $this->wallet_balance,
            'status' => $this->status,
            'is_guest' => (bool) $this->is_guest,
            'orders_count' => $this->orders_count ?? $this->orders()->count(),
            'wishlist_count' => $this->wishlist_count ?? $this->wishlist()->count(),
        ];
    }
}
