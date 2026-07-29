<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json(
            PaymentGatewayConfig::orderBy('provider')->get()->map(fn ($c) => [
                'provider' => $c->provider,
                'display_name' => $c->display_name,
                'key_id' => $c->key_id,
                'has_secret' => (bool) $c->secret,
                'sandbox_mode' => $c->sandbox_mode,
                'enabled' => $c->enabled,
            ])
        );
    }

    public function update(Request $request, PaymentGatewayConfig $config)
    {
        $data = $request->validate([
            'key_id' => ['sometimes', 'nullable', 'string'],
            'secret' => ['sometimes', 'nullable', 'string'],
            'webhook_secret' => ['sometimes', 'nullable', 'string'],
            'sandbox_mode' => ['sometimes', 'boolean'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        $config->update($data);

        return response()->json(['message' => $config->display_name.' settings saved']);
    }

    public function test(PaymentGatewayConfig $config)
    {
        if (! $config->key_id || ! $config->secret) {
            return response()->json(['message' => 'Add API credentials before testing the connection.'], 422);
        }

        if (in_array($config->provider, ['razorpay', 'razorpay_route'])) {
            return $this->testRazorpay($config);
        }

        return response()->json(['message' => 'Credentials saved. Live verification isn\'t implemented yet for '.$config->display_name.'.']);
    }

    protected function testRazorpay(PaymentGatewayConfig $config)
    {
        try {
            $response = Http::withBasicAuth($config->key_id, $config->secret)
                ->get('https://api.razorpay.com/v1/payments', ['count' => 1]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Could not reach Razorpay: '.$e->getMessage()], 502);
        }

        if ($response->status() === 401) {
            return response()->json(['message' => 'Razorpay rejected these credentials. Double-check the Key ID and Secret Key.'], 422);
        }

        if (! $response->successful()) {
            return response()->json(['message' => 'Razorpay returned an unexpected error (HTTP '.$response->status().').'], 502);
        }

        return response()->json(['message' => 'Razorpay credentials verified successfully.']);
    }
}
