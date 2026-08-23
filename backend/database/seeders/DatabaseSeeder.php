<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            HsnCodeSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            SellerSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            BannerSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            ShippingZoneSeeder::class,
            CommissionTierSeeder::class,
            PaymentGatewayConfigSeeder::class,
            ShippingConfigSeeder::class,
            ShiprocketConfigSeeder::class,
            NotificationSeeder::class,
            AppSettingSeeder::class,
            ActivitySeeder::class,
            BlogPostSeeder::class,
        ]);
    }
}
