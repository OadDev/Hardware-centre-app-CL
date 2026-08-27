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
}
