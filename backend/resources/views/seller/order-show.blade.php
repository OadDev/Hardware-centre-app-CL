@extends('seller.layout')

@section('title', 'Order '.$order->order_no)

@section('content')
<a href="{{ route('seller.orders') }}" class="text-sm font-medium text-brand-600 mb-4 inline-block">&larr; Back to My Orders</a>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6 flex flex-wrap items-center justify-between gap-3">
  <div>
    <div class="font-bold text-lg text-ink-900">{{ $order->order_no }}</div>
    <p class="text-xs text-ink-400 mt-1">Placed {{ $order->placed_at->format('d M Y, h:i A') }}</p>
  </div>
  <span class="px-3 py-1 rounded-full text-xs font-semibold
    {{ $order->status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : (in_array($order->status, ['Cancelled','Returned']) ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
    {{ $order->status }}
  </span>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="font-bold text-ink-900 mb-3">Customer</div>
    <div class="text-sm text-ink-800 font-medium">{{ $order->address->contact_name ?? $order->customer->name ?? 'Guest' }}</div>
    <div class="text-xs text-ink-400 mt-0.5">{{ $order->customer->phone ?? '' }}</div>
    @if($order->customer_email)<div class="text-xs text-ink-400">{{ $order->customer_email }}</div>@endif
    <div class="text-xs text-ink-500 mt-1.5">Payment: <span class="font-medium text-ink-700">{{ strtoupper($order->payment_method) }} · {{ $order->payment_status }}</span></div>
  </div>

  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="font-bold text-ink-900 mb-3">Ship To</div>
    @if($order->address)
    <div class="text-sm text-ink-700">{{ $order->address->line }}</div>
    <div class="text-sm text-ink-700">{{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->pincode }}</div>
    @else
    <p class="text-xs text-ink-300">No address on file.</p>
    @endif
    @if($order->courier)<div class="text-xs text-ink-500 mt-1.5">Courier: <span class="font-medium text-ink-700">{{ $order->courier }}@if($order->awb) ({{ $order->awb }})@endif</span></div>@endif
  </div>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900 mb-4">Your Items ({{ $items->count() }})</div>
  <div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Product</th><th class="font-medium">Qty</th><th class="font-medium">Unit Price</th><th class="font-medium">Line Total</th><th class="font-medium">Commission</th>
    </tr></thead>
    <tbody>
      @foreach($items as $item)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">
          <div class="font-medium text-ink-900">{{ $item->product->name ?? 'Product removed' }}</div>
          @if($item->variant_label)<div class="text-xs text-ink-400">{{ $item->variant_label }}</div>@endif
        </td>
        <td>{{ $item->quantity }}</td>
        <td>₹{{ number_format($item->unit_price, 2) }}</td>
        <td>₹{{ number_format($item->line_total, 2) }}</td>
        <td>₹{{ number_format($item->commission_amount, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-1">Shipping &amp; Billing Documents</div>
  <p class="text-xs text-ink-400 mb-4">Print the packing slip to attach to the parcel when you hand it to the courier.</p>
  <div class="flex flex-wrap gap-2.5">
    <a href="{{ route('seller.orders.packing-slip', $order->order_no) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Print Packing Slip</a>

    @if($isSoleSeller)
      @if($order->invoice)
      <a href="{{ route('seller.orders.invoice.pdf', $order->order_no) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-ink-100 hover:bg-ink-200 text-ink-700 text-sm font-semibold">Download Tax Invoice</a>
      @else
      <form method="POST" action="{{ route('seller.orders.invoice.store', $order->order_no) }}">
        @csrf
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-ink-100 hover:bg-ink-200 text-ink-700 text-sm font-semibold">Generate Tax Invoice</button>
      </form>
      @endif
    @else
    <span class="px-4 py-2.5 rounded-xl bg-ink-50 text-ink-400 text-xs font-medium self-center">Tax invoice for this order is issued by Hardware Centers (it includes other sellers' items too).</span>
    @endif
  </div>
</div>
@endsection
