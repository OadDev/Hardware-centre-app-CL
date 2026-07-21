<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    protected $fillable = [
        'provider', 'display_name', 'key_id', 'secret', 'webhook_secret',
        'extra', 'sandbox_mode', 'enabled',
    ];

    protected $hidden = ['secret', 'webhook_secret'];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'extra' => 'array',
            'sandbox_mode' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'provider';
    }
}
