<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'name', 'subject', 'body', 'variables', 'sms_template_id'];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }
}
