<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_no', 'order_id', 'order_item_id', 'customer_id', 'seller_id',
        'type', 'reason', 'amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'return_no';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
