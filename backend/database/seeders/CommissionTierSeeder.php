<?php

namespace Database\Seeders;

use App\Models\CommissionTier;
use Illuminate\Database\Seeder;

class CommissionTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['name' => 'Standard Sellers', 'percentage' => 10, 'sort_order' => 0],
            ['name' => 'Premium Sellers', 'percentage' => 8, 'sort_order' => 1],
            ['name' => 'New Sellers (0-3mo)', 'percentage' => 15, 'sort_order' => 2],
            ['name' => 'High Volume', 'percentage' => 12, 'sort_order' => 3],
        ];

        foreach ($tiers as $row) {
            CommissionTier::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
