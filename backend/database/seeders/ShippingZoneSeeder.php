<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['zone' => 'Zone A — Metro Cities', 'charge' => 49, 'free_above' => 999, 'sort_order' => 0],
            ['zone' => 'Zone B — State Capitals', 'charge' => 69, 'free_above' => 1499, 'sort_order' => 1],
            ['zone' => 'Zone C — Tier 2/3 Cities', 'charge' => 99, 'free_above' => 1999, 'sort_order' => 2],
            ['zone' => 'Zone D — Remote Areas', 'charge' => 149, 'free_above' => 2999, 'sort_order' => 3],
        ];

        foreach ($zones as $row) {
            ShippingZone::updateOrCreate(
                ['zone' => $row['zone']],
                $row + ['weight_slab' => 'Up to 5kg']
            );
        }
    }
}
