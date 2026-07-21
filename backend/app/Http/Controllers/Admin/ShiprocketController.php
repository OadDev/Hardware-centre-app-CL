<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiprocketConfig;
use Illuminate\Http\Request;

class ShiprocketController extends Controller
{
    public function show()
    {
        $config = ShiprocketConfig::current();

        return response()->json([
            'api_email' => $config->api_email,
            'has_password' => (bool) $config->api_password,
            'default_courier' => $config->default_courier,
            'warehouse_name' => $config->warehouse_name,
            'pickup_address' => $config->pickup_address,
            'auto_assign_courier' => $config->auto_assign_courier,
            'auto_generate_awb' => $config->auto_generate_awb,
            'generate_labels' => $config->generate_labels,
            'live_tracking' => $config->live_tracking,
            'connected' => $config->connected,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'api_email' => ['sometimes', 'nullable', 'email'],
            'api_password' => ['sometimes', 'nullable', 'string'],
            'default_courier' => ['sometimes', 'string'],
            'warehouse_name' => ['sometimes', 'nullable', 'string'],
            'pickup_address' => ['sometimes', 'nullable', 'string'],
            'auto_assign_courier' => ['sometimes', 'boolean'],
            'auto_generate_awb' => ['sometimes', 'boolean'],
            'generate_labels' => ['sometimes', 'boolean'],
            'live_tracking' => ['sometimes', 'boolean'],
        ]);

        ShiprocketConfig::current()->update($data);

        return response()->json(['message' => 'Shiprocket settings saved']);
    }

    public function test()
    {
        $config = ShiprocketConfig::current();

        if (! $config->api_email || ! $config->api_password) {
            return response()->json(['message' => 'Add your Shiprocket API email and password before testing.'], 422);
        }

        return response()->json(['message' => 'Credentials saved. Live verification requires a real Shiprocket account.']);
    }

    public function sync()
    {
        return response()->json(['message' => 'Order sync requires a connected Shiprocket account.']);
    }
}
