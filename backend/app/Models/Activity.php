<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['description', 'seller_id'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
