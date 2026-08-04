<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiprocketConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

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

        try {
            $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => $config->api_email,
                'password' => $config->api_password,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Could not reach Shiprocket: '.$e->getMessage()], 502);
        }

        if (in_array($response->status(), [401, 403], true)) {
            $config->update(['connected' => false]);

            return response()->json(['message' => 'Shiprocket rejected these credentials. Double-check the API email and password.'], 422);
        }

        if (! $response->successful() || ! $response->json('token')) {
            $config->update(['connected' => false]);

            return response()->json(['message' => 'Shiprocket returned an unexpected error (HTTP '.$response->status().').'], 502);
        }

        $config->update(['auth_token' => $response->json('token'), 'connected' => true]);

        return response()->json(['message' => 'Shiprocket credentials verified successfully — account connected.']);
    }

    public function sync()
    {
        $config = ShiprocketConfig::current();

        if (! $config->connected) {
            return response()->json(['message' => 'Connect your Shiprocket account first — test your credentials above.'], 422);
        }

        return response()->json(['message' => 'Credentials are connected, but automatic order sync isn\'t implemented yet.']);
    }
}
