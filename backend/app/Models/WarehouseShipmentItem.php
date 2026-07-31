<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseShipmentItem extends Model
{
    protected $fillable = ['warehouse_shipment_id', 'product_id', 'product_variant_id', 'quantity_expected', 'quantity_received'];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(WarehouseShipment::class, 'warehouse_shipment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
