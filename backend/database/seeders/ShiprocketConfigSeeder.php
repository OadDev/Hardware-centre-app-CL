<?php

namespace Database\Seeders;

use App\Models\ShiprocketConfig;
use Illuminate\Database\Seeder;

class ShiprocketConfigSeeder extends Seeder
{
    public function run(): void
    {
        ShiprocketConfig::current()->update([
            'default_courier' => 'Auto Recommend (Best Rate)',
            'auto_assign_courier' => true,
            'auto_generate_awb' => true,
            'generate_labels' => true,
            'live_tracking' => true,
            'connected' => false,
        ]);
    }
}
