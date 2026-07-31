<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::current()->update([
            'business_name' => 'Hardware Centers Pvt. Ltd.',
            'contact_number' => '+91 98765 43210',
            'support_email' => 'support@hardwarecentre.in',
            'gst_number' => 'GSTIN07AABCH1234N1Z5',
            'invoice_prefix' => 'HC-INV-',
            'currency' => 'INR (₹)',
            'timezone' => 'Asia/Kolkata (IST)',
            'language' => 'English',
            'registered_address' => 'Plot 12, Industrial Estate, New Delhi 110006',
            'maintenance_mode' => false,
            'default_gst_rate' => 18,
            'gst_inclusive' => true,
            'password_min_length' => 8,
            'password_require_special' => true,
            'require_2fa_admins' => false,
            'session_timeout_minutes' => 60,
        ]);
    }
}
