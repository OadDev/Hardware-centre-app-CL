<?php

namespace App\Support;

use App\Models\ShippingConfig;
use App\Models\ShippingZone;
use Illuminate\Support\Collection;

class ShippingCalculator
{
    public const METHODS = ['standard', 'express'];

    /**
     * Resolves the delivery charge/ETA for one shipping method, given the
     * destination state and the cart's totals. This is the single place
     * every surface (cart, checkout, gateway order, the final order) goes
     * through, so what a customer is quoted is always what gets charged.
     */
    public static function resolve(string $method, ?string $state, float $subtotal, float $weightKg, int $qty, ?ShippingConfig $config = null): array
    {
        $config = $config ?? ShippingConfig::current();

        if ($method === 'express' && ! $config->express_enabled) {
            return ['charge' => 0.0, 'etaDays' => 0, 'available' => false];
        }

        if ($state && static::isBlocked($state, $method)) {
            return ['charge' => 0.0, 'etaDays' => 0, 'available' => false];
        }

        if ($method === 'standard' && $config->free_shipping_enabled && $subtotal >= (float) $config->free_shipping_min) {
            return ['charge' => 0.0, 'etaDays' => (int) $config->standard_eta_days, 'available' => true];
        }

        $rule = static::matchingRule($method, $state, $subtotal, $weightKg, $qty);

        if ($rule) {
            $defaultEta = $method === 'express' ? $config->express_eta_days : $config->standard_eta_days;
            $freeAbove = (float) $rule->free_above;
            $charge = ($freeAbove > 0 && $subtotal >= $freeAbove) ? 0.0 : (float) $rule->charge;

            return [
                'charge' => $charge,
                'etaDays' => (int) ($rule->eta_days ?? $defaultEta),
                'available' => true,
            ];
        }

        return [
            'charge' => (float) ($method === 'express' ? $config->express_charge : $config->standard_charge),
            'etaDays' => (int) ($method === 'express' ? $config->express_eta_days : $config->standard_eta_days),
            'available' => true,
        ];
    }

    /**
     * All methods for this state/cart, for showing every delivery option
     * (with its own price/ETA/availability) side by side at checkout.
     */
    public static function resolveAll(?string $state, float $subtotal, float $weightKg, int $qty): array
    {
        $config = ShippingConfig::current();

        return collect(static::METHODS)
            ->mapWithKeys(fn ($method) => [$method => static::resolve($method, $state, $subtotal, $weightKg, $qty, $config)])
            ->all();
    }

    /**
     * @param  Collection  $items  cart items with ->product and ->quantity
     */
    public static function weightFor(Collection $items): float
    {
        return (float) $items->sum(fn ($item) => (float) ($item->product->weight_kg ?? 0.5) * $item->quantity);
    }

    /**
     * An admin blocks a method in a state by adding a rule scoped to
     * exactly that state + method and switching it off — distinct from an
     * "off" rule with no state/method, which just means "not a pricing
     * override right now" and is ignored rather than treated as a block.
     */
    private static function isBlocked(string $state, string $method): bool
    {
        return ShippingZone::query()
            ->where('state', $state)
            ->where('method', $method)
            ->where('is_active', false)
            ->exists();
    }

    private static function matchingRule(string $method, ?string $state, float $subtotal, float $weightKg, int $qty): ?ShippingZone
    {
        return ShippingZone::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('method', $method)->orWhereNull('method'))
            ->where(function ($q) use ($state) {
                if ($state) {
                    $q->where('state', $state)->orWhereNull('state');
                } else {
                    $q->whereNull('state');
                }
            })
            ->where('min_order_value', '<=', $subtotal)
            ->where(fn ($q) => $q->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weightKg))
            ->where(fn ($q) => $q->whereNull('min_qty')->orWhere('min_qty', '<=', $qty))
            ->where(fn ($q) => $q->whereNull('max_qty')->orWhere('max_qty', '>=', $qty))
            // Most specific rule wins: an exact state/method/weight/qty match
            // beats a wildcard on that axis, then the highest qualifying
            // order-value threshold (mirroring the bulk-pricing tier rule),
            // then admin-defined sort order breaks any remaining tie.
            ->orderByRaw('(state IS NOT NULL) DESC')
            ->orderByRaw('(method IS NOT NULL) DESC')
            ->orderByRaw('(max_weight_kg IS NOT NULL) DESC')
            ->orderByRaw('(min_qty IS NOT NULL OR max_qty IS NOT NULL) DESC')
            ->orderByDesc('min_order_value')
            ->orderBy('sort_order')
            ->first();
    }
}
