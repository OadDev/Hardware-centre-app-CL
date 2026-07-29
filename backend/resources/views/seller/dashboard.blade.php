@extends('seller.layout')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Total Revenue</div>
    <div class="text-2xl font-bold text-ink-900 mt-2">₹{{ number_format($revenue) }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Products</div>
    <div class="text-2xl font-bold text-ink-900 mt-2">{{ number_format($productsCount) }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Pending Orders</div>
    <div class="text-2xl font-bold text-ink-900 mt-2">{{ number_format($pendingOrders) }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Pending Settlements</div>
    <div class="text-2xl font-bold text-ink-900 mt-2">{{ number_format($pendingSettlements) }}</div>
  </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Wallet Balance</div>
    <div class="text-xl font-bold text-ink-900 mt-2">₹{{ number_format($seller->wallet_balance, 2) }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Rating</div>
    <div class="text-xl font-bold text-ink-900 mt-2">{{ number_format($seller->rating, 1) }} ★</div>
  </div>
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="text-xs text-ink-400 font-semibold uppercase tracking-wide">Commission Rate</div>
    <div class="text-xl font-bold text-ink-900 mt-2">{{ number_format($seller->commission_pct, 1) }}%</div>
  </div>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Recent Order Items</div>
  @if($recentItems->isEmpty())
  <p class="text-sm text-ink-400">No orders yet.</p>
  @else
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Order</th><th class="font-medium">Product</th><th class="font-medium">Qty</th><th class="font-medium">Total</th><th class="font-medium">Status</th>
    </tr></thead>
    <tbody>
      @foreach($recentItems as $item)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">{{ $item->order->order_no }}</td>
        <td>{{ $item->product->name }}</td>
        <td>{{ $item->quantity }}</td>
        <td>₹{{ number_format($item->line_total, 2) }}</td>
        <td>{{ $item->order->status }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>
@endsection
