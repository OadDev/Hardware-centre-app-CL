<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    protected $fillable = [
        'settlement_no', 'seller_id', 'amount', 'status', 'mode', 'utr', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'settlement_no';
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
