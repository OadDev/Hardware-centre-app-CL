<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\NotificationService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function otpChannel(OtpService $otp)
    {
        return response()->json(['channel' => $otp->activeChannel()]);
    }

    public function requestOtp(Request $request, OtpService $otp)
    {
        // The client always tells us which channel it wants (whichever field
        // the customer is actually filling in) — the admin's default only
        // decides which one is shown first, it never blocks the other.
        $channel = $request->input('channel') === 'sms' ? 'sms' : ($request->input('channel') === 'email' ? 'email' : $otp->activeChannel());

        if ($channel === 'sms') {
            $data = $request->validate(['phone' => ['required', 'string', 'min:8', 'max:15']]);
            $contact = OtpService::normalizeIndianMobile($data['phone']);
        } else {
            $data = $request->validate(['email' => ['required', 'email']]);
            $contact = strtolower($data['email']);
        }

        try {
            $code = $otp->send($contact, $channel);
        } catch (Throwable $e) {
            Log::error('OTP send failed: '.$e->getMessage());

            return response()->json(['message' => 'Could not send the verification code. Please try again shortly.'], 500);
        }

        return response()->json([
            'message' => $channel === 'sms'
                ? 'A verification code has been sent via SMS.'
                : 'A verification code has been sent to your email.',
            'channel' => $channel,
            'debug_otp' => app()->environment(['local', 'testing']) ? $code : null,
        ]);
    }

    public function verifyOtp(Request $request, OtpService $otp, NotificationService $notifications)
    {
        $channel = $request->input('channel') === 'sms' ? 'sms' : ($request->input('channel') === 'email' ? 'email' : $otp->activeChannel());

        if ($channel === 'sms') {
            $data = $request->validate([
                'phone' => ['required', 'string'],
                'otp' => ['required', 'digits:6'],
            ]);
            $contact = OtpService::normalizeIndianMobile($data['phone']);
        } else {
            $data = $request->validate([
                'email' => ['required', 'email'],
                'otp' => ['required', 'digits:6'],
            ]);
            $contact = strtolower($data['email']);
        }

        if (! $otp->verify($contact, $data['otp'], $channel)) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        if ($channel === 'sms') {
            $customer = Customer::firstOrCreate(
                ['phone' => $contact],
                [
                    'customer_code' => 'CUS-'.(5000 + Customer::count()),
                    'name' => 'Customer '.substr($contact, -4),
                    'email' => $contact.'@phone.hardwarecentre.in',
                    'status' => 'Active',
                    'is_guest' => false,
                ]
            );
            $customer->forceFill(['phone_verified_at' => now()])->save();
        } else {
            $customer = Customer::firstOrCreate(
                ['email' => $contact],
                [
                    'customer_code' => 'CUS-'.(5000 + Customer::count()),
                    'name' => Str::of($contact)->before('@')->replace(['.', '_'], ' ')->title(),
                    'status' => 'Active',
                    'is_guest' => false,
                ]
            );
            $customer->forceFill(['email_verified_at' => now()])->save();
        }

        if ($customer->wasRecentlyCreated) {
            $notifications->notify('welcome_email', $customer->email, $customer->phone, [
                'customer_name' => $customer->name,
            ], $customer->onesignal_player_id);
        }

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

    public function updateMe(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
        ]);

        $request->user()->update($data);

        return new CustomerResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
