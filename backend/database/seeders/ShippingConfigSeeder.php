<?php

namespace Database\Seeders;

use App\Models\ShippingConfig;
use Illuminate\Database\Seeder;

class ShippingConfigSeeder extends Seeder
{
    public function run(): void
    {
        ShippingConfig::current()->update([
            'cod_enabled' => true,
            'cod_fee' => 25,
            'packaging_fee' => 15,
            'packaging_apply_all' => false,
            'free_shipping_enabled' => true,
            'free_shipping_min' => 5000,
        ]);
    }
}
