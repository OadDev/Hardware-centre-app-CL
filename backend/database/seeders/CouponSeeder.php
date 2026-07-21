<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'HC100', 'type' => 'Flat', 'value' => 100, 'min_order' => 500, 'usage_limit' => 5000, 'used_count' => 1240, 'expires_at' => now()->addMonths(6), 'status' => 'Active'],
            ['code' => 'HC500', 'type' => 'Flat', 'value' => 500, 'min_order' => 2500, 'usage_limit' => 2000, 'used_count' => 412, 'expires_at' => now()->addMonths(3), 'status' => 'Active'],
            ['code' => 'WELCOME150', 'type' => 'Flat', 'value' => 150, 'min_order' => 799, 'usage_limit' => 3000, 'used_count' => 2310, 'expires_at' => now()->addYear(), 'status' => 'Active'],
            ['code' => 'MONSOON25', 'type' => 'Percentage', 'value' => 25, 'min_order' => 1500, 'usage_limit' => 1000, 'used_count' => 96, 'expires_at' => now()->addMonths(2), 'status' => 'Active'],
            ['code' => 'BULKBUY20', 'type' => 'Percentage', 'value' => 20, 'min_order' => 5000, 'usage_limit' => 500, 'used_count' => 178, 'expires_at' => now()->addMonths(4), 'status' => 'Active'],
            ['code' => 'TOOLFEST15', 'type' => 'Percentage', 'value' => 15, 'min_order' => 2000, 'usage_limit' => 800, 'used_count' => 800, 'expires_at' => now()->subMonth(), 'status' => 'Expired'],
        ];

        foreach ($coupons as $row) {
            Coupon::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
