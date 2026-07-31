<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hardwarecentre.in'],
            [
                'name' => 'Hardware Centers Admin',
                'phone' => '9876543210',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Admin']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@hardwarecentre.in'],
            [
                'name' => 'Ops Manager',
                'phone' => '9876500000',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles(['Manager']);

        $warehouseStaff = User::firstOrCreate(
            ['email' => 'warehouse@hardwarecentre.in'],
            [
                'name' => 'Warehouse Staff',
                'phone' => '9876500001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $warehouseStaff->syncRoles(['WarehouseStaff']);
    }
}
