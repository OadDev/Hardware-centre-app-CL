<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpec extends Model
{
    protected $fillable = ['product_id', 'label', 'value', 'sort_order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
