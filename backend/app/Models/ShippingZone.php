<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['zone', 'weight_slab', 'charge', 'free_above', 'sort_order'];

    protected function casts(): array
    {
        return [
            'charge' => 'decimal:2',
            'free_above' => 'decimal:2',
        ];
    }
}
