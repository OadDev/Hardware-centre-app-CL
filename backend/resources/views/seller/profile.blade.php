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
      GST Number: <b class="text-ink-700">{{ $seller->gst_number ?? '—' }}</b> · Commission: <b class="text-ink-700">{{ number_format($seller->commission_pct, 1) }}%</b> · these are managed by Hardware Centre and can't be self-edited.
    </div>
    <button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Save Changes</button>
  </form>
</div>
@endsection
