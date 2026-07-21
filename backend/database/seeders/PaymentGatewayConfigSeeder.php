<?php

namespace Database\Seeders;

use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewayConfigSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['provider' => 'razorpay', 'display_name' => 'Razorpay'],
            ['provider' => 'cashfree', 'display_name' => 'Cashfree'],
            ['provider' => 'razorpay_route', 'display_name' => 'Razorpay Route'],
            ['provider' => 'cashfree_split', 'display_name' => 'Cashfree Easy Split'],
        ];

        foreach ($providers as $row) {
            PaymentGatewayConfig::updateOrCreate(
                ['provider' => $row['provider']],
                $row + ['sandbox_mode' => true, 'enabled' => false]
            );
        }
    }
}
