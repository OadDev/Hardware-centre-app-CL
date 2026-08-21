<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    private const STATUS_BUCKET = [
        'Pending' => 'ongoing',
        'Confirmed' => 'ongoing',
        'Packed' => 'ongoing',
        'Shipped' => 'ongoing',
        'Out for Delivery' => 'ongoing',
        'Delivered' => 'delivered',
        'Cancelled' => 'cancelled',
        'Returned' => 'returned',
    ];

    public function toArray(Request $request): array
    {
        /** @var Order $this */
        return [
            'no' => $this->order_no,
            'status' => self::STATUS_BUCKET[$this->status] ?? 'ongoing',
            'stage' => $this->status,
            'date' => 'Placed '.$this->placed_at->format('d M Y'),
            'sub' => $this->subLine(),
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->product->product_code,
                'q' => $item->quantity,
                'name' => $item->product->name,
                'brand' => $item->product->brand->name,
                'variant' => $item->variant_label,
            ])->all(),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'gst' => (float) $this->gst_amount,
            'delivery' => (float) $this->delivery_charge,
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_method' => $this->shipping_method,
            'eta_days' => $this->eta_days,
            'gst_number' => $this->gst_number,
            'customer_email' => $this->customer_email,
            'timeline' => OrderTimeline::build($this->resource),
        ];
    }

    private function subLine(): string
    {
        return match ($this->status) {
            'Delivered' => 'Rate your order',
            'Cancelled' => 'Refund of ₹'.number_format((float) $this->total, 0).' completed',
            'Returned' => 'Refund of ₹'.number_format((float) $this->total, 0).' processed',
            default => 'Arrives by '.$this->placed_at->addDays($this->eta_days ?: 5)->format('d M'),
        };
    }
}
