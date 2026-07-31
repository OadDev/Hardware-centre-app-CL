<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerCategoryCommission extends Model
{
    protected $fillable = ['seller_id', 'category_id', 'commission_pct'];

    protected function casts(): array
    {
        return [
            'commission_pct' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
