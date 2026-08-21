<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingConfig extends Model
{
    protected $fillable = [
        'cod_enabled', 'cod_fee', 'packaging_fee', 'packaging_apply_all',
        'free_shipping_enabled', 'free_shipping_min',
        'standard_charge', 'standard_eta_days',
        'express_enabled', 'express_charge', 'express_eta_days',
    ];

    protected function casts(): array
    {
        return [
            'cod_enabled' => 'boolean',
            'cod_fee' => 'decimal:2',
            'packaging_fee' => 'decimal:2',
            'packaging_apply_all' => 'boolean',
            'free_shipping_enabled' => 'boolean',
            'free_shipping_min' => 'decimal:2',
            'standard_charge' => 'decimal:2',
            'express_enabled' => 'boolean',
            'express_charge' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
