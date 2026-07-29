<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        return response()->json(AppSetting::current());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'support_email' => ['sometimes', 'nullable', 'email'],
            'gst_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'invoice_prefix' => ['sometimes', 'string', 'max:20'],
            'currency' => ['sometimes', 'string', 'max:20'],
            'timezone' => ['sometimes', 'string', 'max:60'],
            'language' => ['sometimes', 'string', 'max:30'],
            'registered_address' => ['sometimes', 'nullable', 'string'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'smtp_host' => ['sometimes', 'nullable', 'string'],
            'smtp_port' => ['sometimes', 'nullable', 'integer'],
            'smtp_username' => ['sometimes', 'nullable', 'string'],
            'smtp_password' => ['sometimes', 'nullable', 'string'],
            'smtp_encryption' => ['sometimes', 'nullable', 'in:tls,ssl,none'],
            'smtp_from_address' => ['sometimes', 'nullable', 'email'],
            'smtp_from_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'otp_channel' => ['sometimes', 'in:email,sms'],
            'fast2sms_api_key' => ['sometimes', 'nullable', 'string'],
            'ga_id' => ['sometimes', 'nullable', 'string'],
            'gmaps_key' => ['sometimes', 'nullable', 'string'],
            'meta_pixel_id' => ['sometimes', 'nullable', 'string'],
            'fb_capi_token' => ['sometimes', 'nullable', 'string'],
            'password_min_length' => ['sometimes', 'integer', 'min:6', 'max:32'],
            'password_require_special' => ['sometimes', 'boolean'],
            'require_2fa_admins' => ['sometimes', 'boolean'],
            'session_timeout_minutes' => ['sometimes', 'integer', 'min:5'],
        ]);

        $settings = AppSetting::current();
        $settings->update($data);

        return response()->json($settings);
    }
}
