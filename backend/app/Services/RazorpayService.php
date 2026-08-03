<?php

namespace App\Services;

use App\Models\PaymentGatewayConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Thin wrapper around Razorpay's Orders API, using whichever credentials the
 * admin has saved under Settings > Payment Gateway (see PaymentGatewayConfig).
 * We only ever create an order and verify the payment signature here — the
 * actual charge happens client-side via Razorpay's own Checkout, never on
 * our servers, so we never see card/UPI details.
 */
class RazorpayService
{
    private ?PaymentGatewayConfig $config = null;

    private function config(): ?PaymentGatewayConfig
    {
        return $this->config ??= PaymentGatewayConfig::where('provider', 'razorpay')->first();
    }

    public function isEnabled(): bool
    {
        $config = $this->config();

        return (bool) ($config?->enabled) && ! empty($config->key_id) && ! empty($config->secret);
    }

    public function keyId(): string
    {
        return $this->config()->key_id;
    }

    /**
     * Creates a Razorpay order for the given amount and returns its id.
     * Amount is in rupees; Razorpay wants the smallest currency unit (paise).
     */
    public function createOrder(float $amountRupees, string $receipt): string
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Online payments are not available right now. Please choose Cash on Delivery.');
        }

        $config = $this->config();

        try {
            $response = Http::withBasicAuth($config->key_id, $config->secret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => (int) round($amountRupees * 100),
                    'currency' => 'INR',
                    'receipt' => $receipt,
                    'payment_capture' => 1,
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay order creation failed: '.$e->getMessage());

            throw new RuntimeException('Could not reach the payment gateway. Please try again.');
        }

        if (! $response->successful()) {
            Log::error('Razorpay order creation rejected: '.$response->body());

            throw new RuntimeException('Could not start the payment. Please try again.');
        }

        return $response->json('id');
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $config = $this->config();
        if (! $config || empty($config->secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $config->secret);

        return hash_equals($expected, $signature);
    }
}
