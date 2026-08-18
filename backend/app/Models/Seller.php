<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = [
        'seller_code', 'user_id', 'store_name', 'owner_name', 'email', 'phone',
        'city', 'state', 'warehouse_address', 'category_interest', 'gst_number', 'commission_pct',
        'wallet_balance', 'rating', 'approval_status', 'account_status',
        'kyc_status', 'joined_at',
        'pan_number', 'bank_account_holder', 'bank_account_number', 'bank_ifsc',
        'razorpay_account_id', 'razorpay_account_status', 'razorpay_account_error',
    ];

    protected $hidden = ['bank_account_number'];

    protected function casts(): array
    {
        return [
            'commission_pct' => 'decimal:2',
            'wallet_balance' => 'decimal:2',
            'rating' => 'decimal:1',
            'joined_at' => 'datetime',
            'bank_account_number' => 'encrypted',
        ];
    }

    /**
     * Last 4 digits only — the account number itself is encrypted at rest
     * and never sent back to any client in full once saved.
     */
    public function getMaskedBankAccountNumberAttribute(): ?string
    {
        if (! $this->bank_account_number) {
            return null;
        }

        return str_repeat('•', max(0, strlen($this->bank_account_number) - 4)).substr($this->bank_account_number, -4);
    }

    /**
     * Ready for Route once the seller has entered payout bank details and
     * their linked Razorpay account has been created (creation happens via
     * the admin "Link Razorpay Account" action, which then needs Razorpay's
     * own KYC review before it can actually receive transfers).
     */
    public function hasPayoutDetails(): bool
    {
        return (bool) ($this->bank_account_holder && $this->bank_account_number && $this->bank_ifsc && $this->pan_number);
    }

    public function getRouteKeyName(): string
    {
        return 'seller_code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function categoryCommissions(): HasMany
    {
        return $this->hasMany(SellerCategoryCommission::class);
    }

    public function warehouseInventories(): HasMany
    {
        return $this->hasMany(WarehouseInventory::class);
    }

    public function warehouseShipments(): HasMany
    {
        return $this->hasMany(WarehouseShipment::class);
    }

    /**
     * The commission rate that applies to a sale of $category by this
     * seller: an exact override on that category, else an override on its
     * parent (a blanket rate for the whole top-level category), else the
     * seller's own base rate.
     */
    public function commissionRateFor(?Category $category): float
    {
        if (! $category) {
            return (float) $this->commission_pct;
        }

        $categoryIds = array_filter([$category->id, $category->parent_id]);

        $override = $this->categoryCommissions
            ->whereIn('category_id', $categoryIds)
            ->sortBy(fn ($c) => $c->category_id === $category->id ? 0 : 1)
            ->first();

        return $override ? (float) $override->commission_pct : (float) $this->commission_pct;
    }
}
