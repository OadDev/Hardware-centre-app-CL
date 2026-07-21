<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'badge_label', 'type', 'bg_color', 'image_path',
        'link_type', 'link_value', 'sort_order', 'status',
    ];
}
