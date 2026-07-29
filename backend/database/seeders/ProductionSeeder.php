<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs on real deployments via the /setup wizard. Unlike DatabaseSeeder, this
 * only seeds structural/reference data (roles, tax codes, taxonomy, shipping
 * and payment config) — never fabricated sellers, products, customers,
 * orders, coupons, banners, activity, or blog posts.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            HsnCodeSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ShippingZoneSeeder::class,
            CommissionTierSeeder::class,
            PaymentGatewayConfigSeeder::class,
            ShippingConfigSeeder::class,
            ShiprocketConfigSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
