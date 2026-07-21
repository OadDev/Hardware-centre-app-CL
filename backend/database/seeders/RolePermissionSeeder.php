<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['Dashboard', 'Sellers', 'Customers', 'Products', 'Orders', 'Settlements', 'Reports', 'Settings'];

        foreach ($modules as $module) {
            Permission::firstOrCreate(['name' => 'view '.$module, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => 'manage '.$module, 'guard_name' => 'web']);
        }

        $roles = ['Admin', 'Manager', 'Support', 'Accounts', 'Seller'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        /** @var Role $admin */
        $admin = Role::where('name', 'Admin')->first();
        $admin->syncPermissions(Permission::all());

        /** @var Role $manager */
        $manager = Role::where('name', 'Manager')->first();
        $manager->syncPermissions(Permission::whereIn('name', [
            'view Dashboard', 'view Sellers', 'manage Sellers', 'view Customers', 'manage Customers',
            'view Products', 'manage Products', 'view Orders', 'manage Orders',
        ])->get());

        /** @var Role $support */
        $support = Role::where('name', 'Support')->first();
        $support->syncPermissions(Permission::whereIn('name', [
            'view Dashboard', 'view Customers', 'view Orders', 'manage Orders',
        ])->get());

        /** @var Role $accounts */
        $accounts = Role::where('name', 'Accounts')->first();
        $accounts->syncPermissions(Permission::whereIn('name', [
            'view Dashboard', 'view Settlements', 'manage Settlements', 'view Reports',
        ])->get());

        /** @var Role $seller */
        $seller = Role::where('name', 'Seller')->first();
        $seller->syncPermissions(Permission::whereIn('name', [
            'view Products', 'manage Products', 'view Orders',
        ])->get());
    }
}
