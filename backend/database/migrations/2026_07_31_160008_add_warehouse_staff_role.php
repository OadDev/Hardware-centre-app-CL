<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Adds the "Warehouse" permission module and a "WarehouseStaff" role, plus a
 * demo login, on top of the RolePermissionSeeder/AdminUserSeeder output that
 * production already ran via /setup — this runs as a migration (like
 * seed_category_subcategories before it) because there's no way to re-run
 * that wizard against a live install.
 */
return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'view Warehouse', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage Warehouse', 'guard_name' => 'web']);

        $warehousePermissions = Permission::whereIn('name', ['view Warehouse', 'manage Warehouse'])->get();

        $staff = Role::firstOrCreate(['name' => 'WarehouseStaff', 'guard_name' => 'web']);
        $staff->syncPermissions($warehousePermissions);

        $admin = Role::where('name', 'Admin')->first();
        $admin?->givePermissionTo($warehousePermissions);

        $staffUser = User::firstOrCreate(
            ['email' => 'warehouse@hardwarecentre.in'],
            [
                'name' => 'Warehouse Staff',
                'phone' => '9876500001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staffUser->syncRoles(['WarehouseStaff']);
    }

    public function down(): void
    {
        User::where('email', 'warehouse@hardwarecentre.in')->delete();

        $staff = Role::where('name', 'WarehouseStaff')->first();
        $staff?->delete();

        $warehousePermissions = Permission::whereIn('name', ['view Warehouse', 'manage Warehouse'])->get();
        $admin = Role::where('name', 'Admin')->first();
        foreach ($warehousePermissions as $permission) {
            $admin?->revokePermissionTo($permission);
            $permission->delete();
        }
    }
};
