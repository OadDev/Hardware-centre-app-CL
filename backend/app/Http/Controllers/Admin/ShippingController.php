<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingConfig;
use App\Models\ShippingZone;
use App\Support\IndianStates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingController extends Controller
{
    public function index()
    {
        return response()->json([
            'config' => ShippingConfig::current(),
            'zones' => ShippingZone::orderBy('sort_order')->get(),
            'states' => IndianStates::ALL,
        ]);
    }

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'cod_enabled' => ['sometimes', 'boolean'],
            'cod_fee' => ['sometimes', 'numeric', 'min:0'],
            'packaging_fee' => ['sometimes', 'numeric', 'min:0'],
            'packaging_apply_all' => ['sometimes', 'boolean'],
            'free_shipping_enabled' => ['sometimes', 'boolean'],
            'free_shipping_min' => ['sometimes', 'numeric', 'min:0'],
            'standard_charge' => ['sometimes', 'numeric', 'min:0'],
            'standard_eta_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'express_enabled' => ['sometimes', 'boolean'],
            'express_charge' => ['sometimes', 'numeric', 'min:0'],
            'express_eta_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ]);

        $config = ShippingConfig::current();
        $config->update($data);

        return response()->json($config);
    }

    public function storeZone(Request $request)
    {
        $data = $this->validateZone($request);

        return response()->json(ShippingZone::create($data), 201);
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $data = $this->validateZone($request);
        $zone->update($data);

        return response()->json($zone);
    }

    private function validateZone(Request $request): array
    {
        $data = $request->validate([
            'zone' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', Rule::in(IndianStates::ALL)],
            'method' => ['nullable', Rule::in(['standard', 'express'])],
            'weight_slab' => ['nullable', 'string', 'max:60'],
            'charge' => ['required', 'numeric', 'min:0'],
            'free_above' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['sometimes', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],
            'eta_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['weight_slab'] = $data['weight_slab'] ?? '';

        return $data;
    }
}
