<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OnboardingSlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image_path', 'sort_order', 'status',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('uploads')->url($this->image_path) : null;
    }
}
