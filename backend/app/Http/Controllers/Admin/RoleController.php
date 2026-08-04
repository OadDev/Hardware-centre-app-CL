<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NotificationMail;
use App\Models\User;
use App\Support\SmtpMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

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

        $tempPassword = Str::random(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
        ]);
        $user->assignRole($data['role']);

        if (! SmtpMailer::configure()) {
            return response()->json([
                'message' => "User created, but no invitation email was sent — SMTP isn't configured under Settings > Security & SMTP. Share this temporary password with them directly: {$tempPassword}",
            ], 201);
        }

        try {
            Mail::to($user->email)->send(new NotificationMail(
                'You\'ve been invited to the Hardware Centers admin console',
                "Hi {$data['name']},\n\nYou've been added as {$data['role']} on the Hardware Centers admin console.\n\nLogin: ".route('admin.login')."\nEmail: {$user->email}\nTemporary password: {$tempPassword}\n\nPlease log in and change your password as soon as possible."
            ));
        } catch (Throwable $e) {
            Log::error('Team invite email failed: '.$e->getMessage());

            return response()->json([
                'message' => "User created, but the invitation email failed to send. Share this temporary password with them directly: {$tempPassword}",
            ], 201);
        }

        return response()->json(['message' => 'Invitation emailed to '.$data['email']], 201);
    }
}
