<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $sellers = Seller::inRandomOrder()->limit(6)->get();

        if ($sellers->isEmpty()) {
            return;
        }

        $templates = [
            fn ($s) => "New seller <b>{$s}</b> registered and is awaiting KYC approval",
            fn ($s) => "Seller <b>{$s}</b> updated their warehouse address",
            fn () => 'New order placed worth ₹'.random_int(2000, 18000),
            fn ($s) => 'Settlement of ₹'.random_int(5000, 90000)." processed for <b>{$s}</b>",
            fn () => 'Product flagged for low stock — restock recommended',
            fn () => 'Refund request approved for order #'.random_int(90000, 90999),
        ];

        foreach (range(1, 10) as $i) {
            $seller = $sellers->random();
            $template = fake()->randomElement($templates);
            Activity::create([
                'description' => $template($seller->store_name),
                'seller_id' => $seller->id,
                'created_at' => now()->subMinutes(random_int(2, 1400)),
            ]);
        }
    }
}
