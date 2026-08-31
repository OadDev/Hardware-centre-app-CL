<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'order_id', 'customer_id', 'created_by_user_id',
        'business_name', 'business_gst_number', 'business_address',
        'billing_name', 'billing_gst_number', 'billing_address', 'billing_email', 'billing_phone',
        'subtotal', 'discount', 'gst_amount', 'delivery_charge', 'total', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * "{prefix}/{year}/{sequence}", sequence resetting every year and never
     * reused — GST invoices must be numbered strictly in order. Locks the
     * table for the duration of the transaction so two admins generating an
     * invoice at the same moment can't land on the same number.
     */
    public static function nextNumber(): string
    {
        $prefix = rtrim(AppSetting::current()->invoice_prefix ?: 'INV', '-/');
        $year = now()->format('Y');

        return DB::transaction(function () use ($prefix, $year) {
            $count = static::where('invoice_number', 'like', "{$prefix}/{$year}/%")
                ->lockForUpdate()
                ->count();

            return sprintf('%s/%s/%05d', $prefix, $year, $count + 1);
        });
    }

    /**
     * Generates the invoice for an order, or simply returns the one that
     * already exists — invoice numbers are sequential and must never be
     * reused or skipped, so re-generating on every click would be wrong.
     * Shared by the admin console and the seller panel (for single-seller
     * orders) so both call the exact same snapshot logic.
     */
    public static function generateForOrder(Order $order, ?int $createdByUserId = null): self
    {
        if ($order->invoice) {
            return $order->invoice;
        }

        $order->loadMissing(['customer', 'address']);
        $settings = AppSetting::current();
        $address = $order->address;

        return static::create([
            'invoice_number' => static::nextNumber(),
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'created_by_user_id' => $createdByUserId,

            'business_name' => $settings->business_name,
            'business_gst_number' => $settings->gst_number,
            'business_address' => $settings->registered_address,

            'billing_name' => $address?->contact_name ?: $order->customer->name,
            'billing_gst_number' => $order->gst_number ?: $address?->gst_number,
            'billing_address' => $address ? "{$address->line}, {$address->city}, {$address->state} - {$address->pincode}" : null,
            'billing_email' => $order->customer_email ?: $order->customer->email,
            'billing_phone' => $order->customer->phone,

            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'gst_amount' => $order->gst_amount,
            'delivery_charge' => $order->delivery_charge,
            'total' => $order->total,

            'issued_at' => now(),
        ]);
    }

    /**
     * Per-line breakdown for the PDF/API: each order item's share of the
     * order-level discount (prorated by revenue, matching CartCalculator),
     * taxed at that specific product's own effective GST rate.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lineItems(): array
    {
        $order = $this->order;
        $order->loadMissing(['items.product.hsnCode', 'items.productVariant']);
        $taxableBase = (float) $order->subtotal - (float) $order->discount;

        return $order->items->map(function (OrderItem $item) use ($order, $taxableBase) {
            $lineTotal = (float) $item->line_total;
            $lineShare = (float) $order->subtotal > 0 ? $lineTotal / (float) $order->subtotal : 0;
            $lineTaxable = round($lineShare * $taxableBase, 2);
            $gstRate = $item->product?->effectiveGstRate() ?? 0;
            $lineGst = round($lineTaxable * ($gstRate / 100), 2);

            return [
                'product' => $item->product->name ?? 'Product removed',
                'variant' => $item->variant_label,
                'hsnCode' => $item->product?->hsnCode?->code,
                'quantity' => $item->quantity,
                'unitPrice' => (float) $item->unit_price,
                'taxableValue' => $lineTaxable,
                'gstRate' => (float) $gstRate,
                'gstAmount' => $lineGst,
                'lineTotal' => round($lineTaxable + $lineGst, 2),
            ];
        })->all();
    }
}
