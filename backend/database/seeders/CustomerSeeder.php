<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $demo = Customer::updateOrCreate(
            ['email' => 'rajesh@kumarhardware.in'],
            [
                'customer_code' => 'CUS-5000',
                'name' => 'Kumar Hardware',
                'phone' => '9811100001',
                'password' => Hash::make('password'),
                'business_name' => 'Kumar Hardware & Tools',
                'gst_number' => '07AABCK1234M1Z5',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'wallet_balance' => 0,
                'status' => 'Active',
                'is_guest' => false,
                'email_verified_at' => now(),
            ]
        );

        $demo->addresses()->updateOrCreate(
            ['label' => 'Shop'],
            [
                'contact_name' => 'Kumar Hardware & Tools',
                'line' => 'Shop 14, Bhagirath Palace, Chandni Chowk',
                'city' => 'New Delhi',
                'state' => 'Delhi 110006',
                'pincode' => '110006',
                'is_default' => true,
            ]
        );

        $demo->addresses()->updateOrCreate(
            ['label' => 'Warehouse'],
            [
                'contact_name' => 'Kumar Hardware (Godown)',
                'line' => 'Plot 22, Sector 18, Udyog Vihar',
                'city' => 'Gurugram',
                'state' => 'Haryana 122015',
                'pincode' => '122015',
                'is_default' => false,
            ]
        );

        for ($i = 0; $i < 25; $i++) {
            Customer::updateOrCreate(
                ['email' => 'customer'.$i.'@example.in'],
                [
                    'customer_code' => 'CUS-'.(5001 + $i),
                    'name' => fake()->name(),
                    'phone' => '9'.random_int(100000000, 999999999),
                    'password' => Hash::make('password'),
                    'city' => fake()->randomElement(['Mumbai', 'Delhi', 'Bengaluru', 'Chennai', 'Pune', 'Hyderabad']),
                    'state' => fake()->randomElement(['Maharashtra', 'Delhi', 'Karnataka', 'Tamil Nadu', 'Telangana']),
                    'wallet_balance' => random_int(0, 3000),
                    'status' => fake()->randomElement(['Active', 'Active', 'Active', 'Blocked']),
                    'is_guest' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
