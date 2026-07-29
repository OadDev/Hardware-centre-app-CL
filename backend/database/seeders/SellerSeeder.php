<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerSeeder extends Seeder
{
    public static array $realSuppliers = [
        ['store_name' => 'Kumar Power Tools', 'owner_name' => 'Rajesh Kumar', 'city' => 'New Delhi', 'state' => 'Delhi', 'rating' => 4.7, 'years' => 12],
        ['store_name' => 'Shree Electricals', 'owner_name' => 'Suresh Shah', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'rating' => 4.6, 'years' => 9],
        ['store_name' => 'BuildMart Distributors', 'owner_name' => 'Vikram Patil', 'city' => 'Pune', 'state' => 'Maharashtra', 'rating' => 4.5, 'years' => 15],
        ['store_name' => 'ColourHub Traders', 'owner_name' => 'Manoj Desai', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'rating' => 4.4, 'years' => 7],
        ['store_name' => 'SafeGuard Industrial', 'owner_name' => 'Karthik Raman', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'rating' => 4.6, 'years' => 10],
        ['store_name' => 'FastFix Supplies', 'owner_name' => 'Amit Joshi', 'city' => 'Pune', 'state' => 'Maharashtra', 'rating' => 4.5, 'years' => 6],
    ];

    public function run(): void
    {
        $i = 0;
        foreach (self::$realSuppliers as $row) {
            $email = strtolower(str_replace(' ', '.', $row['owner_name'])).'@'.strtolower(str_replace(' ', '', $row['store_name'])).'.in';

            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $row['owner_name'], 'password' => Hash::make('password'), 'is_active' => true]
            );
            $user->syncRoles(['Seller']);

            Seller::updateOrCreate(
                ['store_name' => $row['store_name']],
                [
                    'seller_code' => 'SLR-'.(1000 + $i),
                    'user_id' => $user->id,
                    'owner_name' => $row['owner_name'],
                    'email' => $email,
                    'phone' => '9'.random_int(100000000, 999999999),
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'warehouse_address' => 'Industrial Area Phase '.random_int(1, 4).', '.$row['city'],
                    'gst_number' => strtoupper(chr(65 + $i)).'ABCDE'.random_int(1000, 9999).'F1Z'.random_int(1, 9),
                    'commission_pct' => 10,
                    'wallet_balance' => random_int(5000, 90000),
                    'rating' => $row['rating'],
                    'approval_status' => 'Approved',
                    'account_status' => 'Active',
                    'kyc_status' => 'Verified',
                    'joined_at' => now()->subYears($row['years']),
                ]
            );
            $i++;
        }

        $extra = [
            ['store_name' => 'Sharma Hardware Mart', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'approval' => 'Approved', 'status' => 'Active', 'kyc' => 'Verified'],
            ['store_name' => 'National Tools Co.', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'approval' => 'Approved', 'status' => 'Active', 'kyc' => 'Verified'],
            ['store_name' => 'Apna Hardware Bazaar', 'city' => 'Surat', 'state' => 'Gujarat', 'approval' => 'Pending', 'status' => 'Inactive', 'kyc' => 'Under Review'],
            ['store_name' => 'City Tools Center', 'city' => 'Nagpur', 'state' => 'Maharashtra', 'approval' => 'Approved', 'status' => 'Suspended', 'kyc' => 'Verified'],
            ['store_name' => 'Royal Iron Mart', 'city' => 'Indore', 'state' => 'Madhya Pradesh', 'approval' => 'Rejected', 'status' => 'Inactive', 'kyc' => 'Rejected'],
        ];

        foreach ($extra as $row) {
            Seller::updateOrCreate(
                ['store_name' => $row['store_name']],
                [
                    'seller_code' => 'SLR-'.(1000 + $i),
                    'owner_name' => fake()->name(),
                    'email' => 'contact'.$i.'@'.strtolower(str_replace([' ', '.'], '', $row['store_name'])).'.in',
                    'phone' => '9'.random_int(100000000, 999999999),
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'warehouse_address' => 'Sector '.random_int(1, 40).', '.$row['city'],
                    'gst_number' => strtoupper(chr(65 + $i)).'ABCDE'.random_int(1000, 9999).'F1Z'.random_int(1, 9),
                    'commission_pct' => fake()->randomElement([8, 10, 12, 15]),
                    'wallet_balance' => random_int(0, 40000),
                    'rating' => fake()->randomFloat(1, 3.5, 5.0),
                    'approval_status' => $row['approval'],
                    'account_status' => $row['status'],
                    'kyc_status' => $row['kyc'],
                    'joined_at' => now()->subMonths(random_int(1, 30)),
                ]
            );
            $i++;
        }
    }
}
