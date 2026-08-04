<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'badge_label', 'type', 'bg_color', 'image_path',
        'link_type', 'link_value', 'sort_order', 'status',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('uploads')->url($this->image_path) : null;
    }
}
