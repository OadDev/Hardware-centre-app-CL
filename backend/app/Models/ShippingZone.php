<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'zone', 'state', 'method', 'weight_slab', 'charge', 'free_above',
        'min_order_value', 'max_weight_kg', 'min_qty', 'max_qty', 'eta_days',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'charge' => 'decimal:2',
            'free_above' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'max_weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }
}
