<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\Seller;
use App\Models\ShiprocketConfig;
use App\Support\ShippingCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Creates a real Shiprocket shipment (order → AWB → label) for one seller's
 * portion of an order. A multi-vendor order ships as separate parcels from
 * separate pickup locations, so this is always scoped to a single seller's
 * own items — never the whole order — and keeps its own OrderShipment row
 * per (order, seller) rather than overloading the order's single legacy
 * courier/awb columns.
 *
 * Every step's failure is surfaced as Shiprocket's own error message (not a
 * generic one) so whoever's configuring credentials/pickup location can
 * actually fix it, and progress already made (order created, AWB assigned)
 * is persisted immediately so a retry after a failed step doesn't recreate
 * duplicate Shiprocket orders.
 */
class ShiprocketService
{
    private const BASE = 'https://apiv2.shiprocket.in/v1/external';

    public function createShipmentForSeller(Order $order, Seller $seller, Collection $items): OrderShipment
    {
        $config = ShiprocketConfig::current();

        if (empty($config->api_email) || empty($config->api_password)) {
            throw new RuntimeException("Shiprocket isn't connected — add the API email and password under Admin > Shiprocket Integration first.");
        }
        if (empty($config->warehouse_name)) {
            throw new RuntimeException('Set a Warehouse / Pickup Location name under Admin > Shiprocket Integration — it must exactly match a pickup address already added in your Shiprocket account.');
        }

        $shipment = OrderShipment::firstOrCreate(['order_id' => $order->id, 'seller_id' => $seller->id]);

        if ($shipment->awb) {
            return $shipment;
        }

        try {
            $token = $this->login($config);

            if (! $shipment->shiprocket_shipment_id) {
                $created = $this->createOrder($token, $config, $order, $seller, $items);
                $shipment->update([
                    'shiprocket_order_id' => $created['order_id'] ?? null,
                    'shiprocket_shipment_id' => $created['shipment_id'] ?? null,
                    'status' => 'Order Created',
                    'last_error' => null,
                ]);
            }

            if ($config->auto_generate_awb && $shipment->shiprocket_shipment_id) {
                $awb = $this->assignAwb($token, $shipment->shiprocket_shipment_id, $config);
                $shipment->update([
                    'courier' => $awb['courier_name'] ?? null,
                    'awb' => $awb['awb_code'] ?? null,
                    'status' => 'AWB Assigned',
                ]);
            }

            if ($config->generate_labels && $shipment->shiprocket_shipment_id) {
                $labelUrl = $this->generateLabel($token, $shipment->shiprocket_shipment_id);
                if ($labelUrl) {
                    $shipment->update(['label_url' => $labelUrl, 'status' => 'Label Generated']);
                }
            }
        } catch (Throwable $e) {
            $shipment->update(['last_error' => $e->getMessage()]);
            throw $e;
        }

        if ($shipment->courier) {
            $order->update(['courier' => $shipment->courier, 'awb' => $shipment->awb]);
        }

        return $shipment->fresh();
    }

    private function login(ShiprocketConfig $config): string
    {
        $response = Http::post(self::BASE.'/auth/login', [
            'email' => $config->api_email,
            'password' => $config->api_password,
        ]);

        if (! $response->successful() || ! $response->json('token')) {
            $config->update(['connected' => false]);
            throw new RuntimeException('Shiprocket login failed: '.($response->json('message') ?: 'check the API email/password under Admin > Shiprocket Integration.'));
        }

        $config->update(['auth_token' => $response->json('token'), 'connected' => true]);

        return $response->json('token');
    }

    /**
     * @return array{order_id: ?string, shipment_id: ?string}
     */
    private function createOrder(string $token, ShiprocketConfig $config, Order $order, Seller $seller, Collection $items): array
    {
        $address = $order->address;
        $customer = $order->customer;
        [$firstName, $lastName] = $this->splitName($address?->contact_name ?: $customer->name);

        $subTotal = (float) $items->sum('line_total');
        $weightKg = max(0.1, ShippingCalculator::weightFor($items));

        $response = Http::withToken($token)->post(self::BASE.'/orders/create/adhoc', [
            // Suffixed with the seller so two sellers shipping their own
            // portion of the same marketplace order don't collide on
            // Shiprocket's globally-unique order_id.
            'order_id' => $order->order_no.'-'.$seller->seller_code,
            'order_date' => $order->placed_at->format('Y-m-d H:i'),
            'pickup_location' => $config->warehouse_name,
            'billing_customer_name' => $firstName,
            'billing_last_name' => $lastName,
            'billing_address' => $address?->line ?: 'N/A',
            'billing_city' => $address?->city ?: 'N/A',
            'billing_pincode' => $address?->pincode ?: '000000',
            'billing_state' => $address?->state ?: 'N/A',
            'billing_country' => 'India',
            'billing_email' => $order->customer_email ?: $customer->email ?: 'no-reply@hardwarecenters.com',
            'billing_phone' => $customer->phone,
            'shipping_is_billing' => true,
            'order_items' => $items->map(fn ($item) => [
                'name' => $item->product->name ?? 'Product',
                'sku' => $item->productVariant?->sku ?: $item->product?->sku ?: $item->product?->product_code ?: 'SKU',
                'units' => $item->quantity,
                'selling_price' => (float) $item->unit_price,
            ])->values()->all(),
            'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => $subTotal,
            'length' => 15,
            'breadth' => 10,
            'height' => 10,
            'weight' => $weightKg,
        ]);

        if (! $response->successful() || $response->json('status_code') === 0 || $response->json('order_id') === null) {
            throw new RuntimeException('Shiprocket rejected the order: '.$this->extractError($response));
        }

        return [
            'order_id' => (string) $response->json('order_id'),
            'shipment_id' => (string) $response->json('shipment_id'),
        ];
    }

    /**
     * @return array{awb_code: ?string, courier_name: ?string}
     */
    private function assignAwb(string $token, string $shipmentId, ShiprocketConfig $config): array
    {
        $payload = ['shipment_id' => $shipmentId];

        $response = Http::withToken($token)->post(self::BASE.'/courier/assign/awb', $payload);

        $data = $response->json('response.data');

        if (! $response->successful() || ! $data || empty($data['awb_code'])) {
            throw new RuntimeException('Shiprocket order was created, but AWB assignment failed: '.$this->extractError($response).' You can assign a courier manually from your Shiprocket dashboard.');
        }

        return ['awb_code' => $data['awb_code'], 'courier_name' => $data['courier_name'] ?? $config->default_courier];
    }

    private function generateLabel(string $token, string $shipmentId): ?string
    {
        $response = Http::withToken($token)->post(self::BASE.'/courier/generate/label', [
            'shipment_id' => [$shipmentId],
        ]);

        return $response->successful() ? $response->json('label_url') : null;
    }

    private function extractError($response): string
    {
        return $response->json('message')
            ?: (is_array($response->json('errors')) ? collect($response->json('errors'))->flatten()->implode(' ') : null)
            ?: 'HTTP '.$response->status();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0] ?: 'Customer', $parts[1] ?? '.'];
    }
}
