<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HsnCode extends Model
{
    protected $fillable = ['code', 'description', 'gst_rate'];

    protected function casts(): array
    {
        return [
            'gst_rate' => 'decimal:2',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
