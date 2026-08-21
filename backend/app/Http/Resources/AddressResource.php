<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Address $this */
        return [
            'id' => $this->id,
            'label' => $this->label,
            'name' => $this->contact_name,
            'line' => $this->line,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'gst_number' => $this->gst_number,
            'email' => $this->email,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
