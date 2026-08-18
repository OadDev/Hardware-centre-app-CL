<?php

namespace App\Services;

use App\Models\PaymentGatewayConfig;
use App\Models\Seller;
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

    /**
     * ---------------- Razorpay Route (marketplace split payments) ----------------
     * Everything below needs Razorpay's separate "Route" product approved on
     * this account — a business review distinct from standard Payments — and
     * only runs once an admin explicitly links a seller's account. It has
     * NOT been exercised against Razorpay's live API from this codebase: the
     * request/response shapes follow Razorpay's public Route docs as closely
     * as possible, but field requirements do vary by business type and can
     * change. Treat the first few real linkings as something to debug against
     * the actual error messages Razorpay returns (surfaced via the "error"
     * key below), not as a finished, verified integration.
     */

    /**
     * Creates a Razorpay linked account for a seller, submits their
     * stakeholder KYC, and requests the Route product with their settlement
     * bank details. Returns whatever Razorpay reports back at each step —
     * actual fund-transfer readiness still depends on Razorpay's own KYC
     * review after this call returns (their status moves to "activated"
     * asynchronously, not immediately).
     *
     * @return array{ok: bool, account_id?: ?string, status?: string, error?: ?string}
     */
    public function createLinkedAccount(Seller $seller): array
    {
        if (! $this->isEnabled()) {
            return ['ok' => false, 'error' => 'Razorpay is not configured/enabled under Settings > Payment Gateway.'];
        }
        if (! $seller->hasPayoutDetails()) {
            return ['ok' => false, 'error' => 'Seller is missing PAN, bank account, or IFSC details.'];
        }

        $config = $this->config();
        $address = [
            'street1' => $seller->warehouse_address ?: $seller->city ?: 'NA',
            'city' => $seller->city ?: 'NA',
            'state' => $seller->state ?: 'NA',
            'postal_code' => '000000',
            'country' => 'IN',
        ];

        try {
            $accountResp = Http::withBasicAuth($config->key_id, $config->secret)
                ->post('https://api.razorpay.com/v2/accounts', [
                    'email' => $seller->email,
                    'phone' => preg_replace('/\D/', '', $seller->phone),
                    'type' => 'route',
                    'reference_id' => $seller->seller_code,
                    'legal_business_name' => $seller->store_name,
                    'business_type' => 'individual',
                    'contact_name' => $seller->owner_name,
                    'profile' => [
                        'category' => 'retail',
                        'subcategory' => 'building_material',
                        'addresses' => ['registered' => $address],
                    ],
                    'legal_info' => array_filter([
                        'pan' => $seller->pan_number,
                        'gst' => $seller->gst_number,
                    ]),
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay linked account creation failed: '.$e->getMessage());

            return ['ok' => false, 'error' => 'Could not reach Razorpay. Please try again.'];
        }

        if (! $accountResp->successful()) {
            Log::error('Razorpay linked account creation rejected: '.$accountResp->body());

            return ['ok' => false, 'error' => $accountResp->json('error.description') ?: $accountResp->body()];
        }

        $accountId = $accountResp->json('id');

        try {
            $stakeholderResp = Http::withBasicAuth($config->key_id, $config->secret)
                ->post("https://api.razorpay.com/v2/accounts/{$accountId}/stakeholders", [
                    'name' => $seller->owner_name,
                    'email' => $seller->email,
                    'addresses' => ['residential' => $address],
                    'kyc' => array_filter(['pan' => $seller->pan_number]),
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay stakeholder creation failed: '.$e->getMessage());

            return ['ok' => false, 'account_id' => $accountId, 'error' => 'Linked account created, but adding the stakeholder failed — could not reach Razorpay.'];
        }

        if (! $stakeholderResp->successful()) {
            Log::error('Razorpay stakeholder creation rejected: '.$stakeholderResp->body());
            $msg = $stakeholderResp->json('error.description') ?: $stakeholderResp->body();

            return ['ok' => false, 'account_id' => $accountId, 'error' => 'Linked account created, but the stakeholder step failed: '.$msg];
        }

        try {
            $productResp = Http::withBasicAuth($config->key_id, $config->secret)
                ->post("https://api.razorpay.com/v2/accounts/{$accountId}/products", [
                    'product_name' => 'route',
                    'tnc_accepted' => true,
                    'settlements' => [
                        'account_number' => $seller->bank_account_number,
                        'ifsc_code' => $seller->bank_ifsc,
                        'beneficiary_name' => $seller->bank_account_holder,
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay route product request failed: '.$e->getMessage());

            return ['ok' => true, 'account_id' => $accountId, 'status' => 'Pending', 'error' => 'Account and stakeholder created, but the Route product request failed — link again to resubmit it.'];
        }

        $status = $productResp->successful() ? ($productResp->json('activation_status') ?: 'Pending') : 'Pending';
        $productError = $productResp->successful() ? null : ($productResp->json('error.description') ?: $productResp->body());

        return ['ok' => true, 'account_id' => $accountId, 'status' => ucfirst($status), 'error' => $productError];
    }

    /**
     * Splits an already-verified/captured payment across sellers. Each
     * transfer targets one seller's linked account for their line-item
     * total minus commission. Returns one result per input transfer in the
     * same order — a transfer to a seller whose Route account isn't active
     * yet comes back ok=false and the caller should fall back to recording
     * a manual settlement instead.
     *
     * @param  array<int, array{account: string, amount: float, notes?: array}>  $transfers
     * @return array<int, array{ok: bool, transfer_id?: ?string, error?: ?string}>
     */
    public function transfer(string $paymentId, array $transfers): array
    {
        if (empty($transfers)) {
            return [];
        }

        $config = $this->config();

        try {
            $response = Http::withBasicAuth($config->key_id, $config->secret)
                ->post("https://api.razorpay.com/v1/payments/{$paymentId}/transfers", [
                    'transfers' => array_map(fn ($t) => [
                        'account' => $t['account'],
                        'amount' => (int) round($t['amount'] * 100),
                        'currency' => 'INR',
                        'notes' => $t['notes'] ?? [],
                    ], $transfers),
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay transfer request failed: '.$e->getMessage());

            return array_fill(0, count($transfers), ['ok' => false, 'error' => 'Could not reach Razorpay.']);
        }

        if (! $response->successful()) {
            Log::error('Razorpay transfer rejected: '.$response->body());
            $msg = $response->json('error.description') ?: $response->body();

            return array_fill(0, count($transfers), ['ok' => false, 'error' => $msg]);
        }

        $items = $response->json('items', []);
        $results = [];
        foreach ($transfers as $i => $t) {
            $item = $items[$i] ?? null;
            $results[] = $item
                ? ['ok' => true, 'transfer_id' => $item['id'] ?? null]
                : ['ok' => false, 'error' => 'No transfer result returned for this seller.'];
        }

        return $results;
    }
}
