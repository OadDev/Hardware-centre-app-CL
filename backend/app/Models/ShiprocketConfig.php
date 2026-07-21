<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiprocketConfig extends Model
{
    protected $fillable = [
        'api_email', 'api_password', 'auth_token', 'default_courier',
        'warehouse_name', 'pickup_address', 'auto_assign_courier',
        'auto_generate_awb', 'generate_labels', 'live_tracking', 'connected',
    ];

    protected $hidden = ['api_password', 'auth_token'];

    protected function casts(): array
    {
        return [
            'api_password' => 'encrypted',
            'auth_token' => 'encrypted',
            'auto_assign_courier' => 'boolean',
            'auto_generate_awb' => 'boolean',
            'generate_labels' => 'boolean',
            'live_tracking' => 'boolean',
            'connected' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
