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
     * destination (state, and optionally the more specific city/pincode)
     * and the cart's totals. This is the single place every surface (cart,
     * checkout, gateway order, the final order) goes through, so what a
     * customer is quoted is always what gets charged.
     */
    public static function resolve(string $method, ?string $state, float $subtotal, float $weightKg, int $qty, ?ShippingConfig $config = null, ?string $city = null, ?string $pincode = null): array
    {
        $config = $config ?? ShippingConfig::current();

        if ($method === 'express' && ! $config->express_enabled) {
            return ['charge' => 0.0, 'etaDays' => 0, 'available' => false];
        }

        if (static::isBlocked($method, $state, $city, $pincode)) {
            return ['charge' => 0.0, 'etaDays' => 0, 'available' => false];
        }

        if ($method === 'standard' && $config->free_shipping_enabled && $subtotal >= (float) $config->free_shipping_min) {
            return ['charge' => 0.0, 'etaDays' => (int) $config->standard_eta_days, 'available' => true];
        }

        $rule = static::matchingRule($method, $state, $city, $pincode, $subtotal, $weightKg, $qty);

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
     * All methods for this destination/cart, for showing every delivery
     * option (with its own price/ETA/availability) side by side at checkout.
     */
    public static function resolveAll(?string $state, float $subtotal, float $weightKg, int $qty, ?string $city = null, ?string $pincode = null): array
    {
        $config = ShippingConfig::current();

        return collect(static::METHODS)
            ->mapWithKeys(fn ($method) => [$method => static::resolve($method, $state, $subtotal, $weightKg, $qty, $config, $city, $pincode)])
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
     * An admin blocks a method in a location by adding a rule scoped to
     * exactly that location (state, or more specifically city/pincode) +
     * method and switching it off — distinct from an "off" rule with no
     * location/method, which just means "not a pricing override right now"
     * and is ignored rather than treated as a block.
     */
    private static function isBlocked(string $method, ?string $state, ?string $city, ?string $pincode): bool
    {
        if (! $state && ! $city && ! $pincode) {
            return false;
        }

        return ShippingZone::query()
            ->where('method', $method)
            ->where('is_active', false)
            ->where(fn ($q) => static::scopeToLocation($q, $state, $city, $pincode))
            ->exists();
    }

    /**
     * Restricts a query to rules whose location scoping (any mix of
     * state/city/pincode, each independently nullable-as-wildcard) matches
     * the given destination. A rule only matches if every field it sets is
     * an exact match — it's the caller's orderBy that then picks the most
     * specific match among several that qualify.
     */
    private static function scopeToLocation($q, ?string $state, ?string $city, ?string $pincode)
    {
        return $q
            ->where(fn ($w) => $state ? $w->where('state', $state)->orWhereNull('state') : $w->whereNull('state'))
            ->where(fn ($w) => $city ? $w->whereRaw('LOWER(city) = ?', [mb_strtolower($city)])->orWhereNull('city') : $w->whereNull('city'))
            ->where(fn ($w) => $pincode ? $w->where('pincode', $pincode)->orWhereNull('pincode') : $w->whereNull('pincode'));
    }

    private static function matchingRule(string $method, ?string $state, ?string $city, ?string $pincode, float $subtotal, float $weightKg, int $qty): ?ShippingZone
    {
        return ShippingZone::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('method', $method)->orWhereNull('method'))
            ->where(fn ($q) => static::scopeToLocation($q, $state, $city, $pincode))
            ->where('min_order_value', '<=', $subtotal)
            ->where(fn ($q) => $q->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weightKg))
            ->where(fn ($q) => $q->whereNull('min_qty')->orWhere('min_qty', '<=', $qty))
            ->where(fn ($q) => $q->whereNull('max_qty')->orWhere('max_qty', '>=', $qty))
            // Most specific rule wins: an exact pincode/city/state/method/
            // weight/qty match beats a wildcard on that axis, then the
            // highest qualifying order-value threshold (mirroring the
            // bulk-pricing tier rule), then admin-defined sort order breaks
            // any remaining tie.
            ->orderByRaw('(pincode IS NOT NULL) DESC')
            ->orderByRaw('(city IS NOT NULL) DESC')
            ->orderByRaw('(state IS NOT NULL) DESC')
            ->orderByRaw('(method IS NOT NULL) DESC')
            ->orderByRaw('(max_weight_kg IS NOT NULL) DESC')
            ->orderByRaw('(min_qty IS NOT NULL OR max_qty IS NOT NULL) DESC')
            ->orderByDesc('min_order_value')
            ->orderBy('sort_order')
            ->first();
    }
}
