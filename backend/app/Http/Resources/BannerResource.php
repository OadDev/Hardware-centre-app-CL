<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Banner $this */
        return [
            'id' => $this->id,
            'badge' => $this->badge_label,
            'title' => $this->title,
            'sub' => $this->subtitle,
            'bg' => $this->bg_color,
            'image_url' => $this->image_url,
            'type' => $this->type,
            'status' => $this->status,
        ];
    }
}
