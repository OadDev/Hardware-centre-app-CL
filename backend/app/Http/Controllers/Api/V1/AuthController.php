<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function requestOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($data['email']);
        $code = (string) random_int(100000, 999999);

        Cache::put('otp:'.$email, Hash::make($code), now()->addMinutes(5));

        Log::info("Hardware Centre OTP for {$email}: {$code}");

        return response()->json([
            'message' => 'A verification code has been sent to your email.',
            'debug_otp' => app()->environment(['local', 'testing']) ? $code : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower($data['email']);
        $hashed = Cache::get('otp:'.$email);

        if (! $hashed || ! Hash::check($data['otp'], $hashed)) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        Cache::forget('otp:'.$email);

        $customer = Customer::firstOrCreate(
            ['email' => $email],
            [
                'customer_code' => 'CUS-'.(5000 + Customer::count()),
                'name' => Str::of($email)->before('@')->replace(['.', '_'], ' ')->title(),
                'status' => 'Active',
                'is_guest' => false,
            ]
        );

        $customer->forceFill(['email_verified_at' => now()])->save();

        $token = $customer->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function guest(Request $request)
    {
        $customer = Customer::create([
            'customer_code' => 'CUS-'.(5000 + Customer::count()),
            'name' => 'Guest',
            'email' => 'guest_'.Str::uuid().'@guest.hardwarecentre.in',
            'status' => 'Active',
            'is_guest' => true,
        ]);

        $token = $customer->createToken('guest')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function me(Request $request)
    {
        return new CustomerResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
