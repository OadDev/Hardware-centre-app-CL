<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionTier extends Model
{
    protected $fillable = ['name', 'percentage', 'sort_order'];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }
}
