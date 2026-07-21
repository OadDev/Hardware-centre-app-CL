<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Request;

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

        return response()->json(['message' => 'Credentials saved. Live verification requires a real '.$config->display_name.' sandbox account.']);
    }
}
