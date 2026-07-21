<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $fillable = ['key', 'name', 'description', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }
}
