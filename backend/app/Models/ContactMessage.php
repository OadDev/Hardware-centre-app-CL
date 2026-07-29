<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'source', 'name', 'email', 'phone', 'order_no', 'subject', 'message', 'status',
    ];
}
