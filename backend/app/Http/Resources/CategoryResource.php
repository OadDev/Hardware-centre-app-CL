<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Category $this */
        return [
            'id' => $this->icon,
            'name' => $this->name,
            'parent_id' => $this->relationLoaded('parent') ? $this->parent?->icon : null,
            'count' => $this->products_count ?? $this->products()->count(),
            'status' => $this->status,
            'icon_image' => $this->icon_url,
            'icon_class' => $this->icon_class,
        ];
    }
}
