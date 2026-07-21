<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingConfig;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        return response()->json([
            'config' => ShippingConfig::current(),
            'zones' => ShippingZone::orderBy('sort_order')->get(),
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
        return $request->validate([
            'zone' => ['required', 'string', 'max:120'],
            'weight_slab' => ['sometimes', 'string', 'max:60'],
            'charge' => ['required', 'numeric', 'min:0'],
            'free_above' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
