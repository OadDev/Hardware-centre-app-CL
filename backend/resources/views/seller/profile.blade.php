@extends('seller.layout')

@section('title', 'Store Profile')

@section('content')
<div class="bg-white rounded-2xl border border-ink-200/70 p-5 max-w-xl">
  <div class="font-bold text-ink-900 mb-4">Store details</div>
  <form method="POST" action="{{ route('seller.profile.update') }}" class="space-y-4">
    @csrf
    @method('PATCH')
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Store Name</label><input name="store_name" value="{{ old('store_name', $seller->store_name) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Owner Name</label><input name="owner_name" value="{{ old('owner_name', $seller->owner_name) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Phone</label><input name="phone" value="{{ old('phone', $seller->phone) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-medium text-ink-500 mb-1">City</label><input name="city" value="{{ old('city', $seller->city) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
      <div><label class="block text-xs font-medium text-ink-500 mb-1">State</label><input name="state" value="{{ old('state', $seller->state) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    </div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Warehouse Address</label><textarea name="warehouse_address" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200">{{ old('warehouse_address', $seller->warehouse_address) }}</textarea></div>
    <div class="pt-2 border-t border-ink-100 text-xs text-ink-400">
      GST Number: <b class="text-ink-700">{{ $seller->gst_number ?? '—' }}</b> · Commission: <b class="text-ink-700">{{ number_format($seller->commission_pct, 1) }}%</b> · these are managed by Hardware Centers and can't be self-edited.
    </div>
    <button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Save Changes</button>
  </form>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 max-w-xl mt-6">
  <div class="flex items-center justify-between mb-1">
    <div class="font-bold text-ink-900">Payout details</div>
    @php
      $rpStatus = $seller->razorpay_account_status ?? 'Not Linked';
      $rpBadge = match(true) {
        str_contains(strtolower($rpStatus), 'activ') => 'bg-emerald-50 text-emerald-700',
        $rpStatus === 'Not Linked' => 'bg-ink-100 text-ink-500',
        default => 'bg-amber-50 text-amber-700',
      };
    @endphp
    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rpBadge }}">{{ $rpStatus }}</span>
  </div>
  <p class="text-xs text-ink-400 mb-4">Customer payments are collected by Hardware Centers via Razorpay. After deducting commission, the balance is settled to this bank account. Save your details below, then Hardware Centers will link your account for payouts.</p>
  @if($seller->razorpay_account_error)
  <div class="mb-4 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs">{{ $seller->razorpay_account_error }}</div>
  @endif
  <form method="POST" action="{{ route('seller.profile.payout.update') }}" class="space-y-4">
    @csrf
    @method('PATCH')
    <div><label class="block text-xs font-medium text-ink-500 mb-1">PAN Number</label><input name="pan_number" value="{{ old('pan_number', $seller->pan_number) }}" required maxlength="10" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 uppercase focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Account Holder Name</label><input name="bank_account_holder" value="{{ old('bank_account_holder', $seller->bank_account_holder) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-medium text-ink-500 mb-1">Account Number</label><input name="bank_account_number" placeholder="{{ $seller->masked_bank_account_number ?? 'e.g. 000123456789' }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
      <div><label class="block text-xs font-medium text-ink-500 mb-1">IFSC Code</label><input name="bank_ifsc" value="{{ old('bank_ifsc', $seller->bank_ifsc) }}" required maxlength="11" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 uppercase focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    </div>
    @if($seller->masked_bank_account_number)
    <p class="text-xs text-ink-400">On file: {{ $seller->masked_bank_account_number }}. Re-enter your account number above to keep it — saving this form always replaces it with whatever you type.</p>
    @endif
    <button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Save Payout Details</button>
  </form>
</div>
@endsection
