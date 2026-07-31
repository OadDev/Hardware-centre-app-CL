<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseShipment extends Model
{
    protected $fillable = ['shipment_no', 'warehouse_id', 'seller_id', 'status', 'notes', 'expected_at', 'received_at'];

    protected function casts(): array
    {
        return [
            'expected_at' => 'date',
            'received_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'shipment_no';
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseShipmentItem::class);
    }
}
