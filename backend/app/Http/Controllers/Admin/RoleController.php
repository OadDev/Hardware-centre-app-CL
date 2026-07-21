<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'roles' => Role::withCount('users')->get()->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'members' => $r->users_count,
                'permissions' => $r->permissions->pluck('name'),
            ]),
            'permission_modules' => Permission::all()->pluck('name')->unique()->values(),
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json(['message' => 'Permissions updated for '.$role->name]);
    }

    public function invite(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(str()->random(16)),
        ]);
        $user->assignRole($data['role']);

        return response()->json(['message' => 'Invitation sent to '.$data['email']], 201);
    }
}
