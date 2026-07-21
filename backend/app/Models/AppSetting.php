<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'business_name', 'contact_number', 'support_email', 'gst_number',
        'invoice_prefix', 'currency', 'timezone', 'language', 'registered_address',
        'logo_path', 'maintenance_mode', 'smtp_host', 'smtp_port', 'smtp_username',
        'smtp_password', 'ga_id', 'gmaps_key', 'meta_pixel_id', 'fb_capi_token',
        'default_gst_rate', 'gst_inclusive', 'password_min_length',
        'password_require_special', 'require_2fa_admins', 'session_timeout_minutes',
    ];

    protected $hidden = ['smtp_password', 'fb_capi_token'];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'smtp_password' => 'encrypted',
            'fb_capi_token' => 'encrypted',
            'default_gst_rate' => 'decimal:2',
            'gst_inclusive' => 'boolean',
            'password_require_special' => 'boolean',
            'require_2fa_admins' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
