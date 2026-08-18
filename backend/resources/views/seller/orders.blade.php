@extends('seller.layout')

@section('title', 'My Orders')

@section('content')
<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Order Items ({{ $items->total() }})</div>
  @if($items->isEmpty())
  <p class="text-sm text-ink-400">No orders for your products yet.</p>
  @else
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Order</th><th class="font-medium">Customer</th><th class="font-medium">Product</th><th class="font-medium">Qty</th><th class="font-medium">Line Total</th><th class="font-medium">Commission</th><th class="font-medium">Status</th><th class="font-medium">Placed</th>
    </tr></thead>
    <tbody>
      @foreach($items as $item)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">{{ $item->order->order_no }}</td>
        <td>
          <div class="font-medium text-ink-900">{{ $item->order->customer->name ?? 'Guest' }}</div>
          <div class="text-xs text-ink-400">{{ $item->order->customer->phone ?? $item->order->customer->email ?? '' }}</div>
        </td>
        <td>
          <div class="font-medium text-ink-900">{{ $item->product->name }}</div>
          @if($item->variant_label)<div class="text-xs text-ink-400">{{ $item->variant_label }}</div>@endif
        </td>
        <td>{{ $item->quantity }}</td>
        <td>₹{{ number_format($item->line_total, 2) }}</td>
        <td>₹{{ number_format($item->commission_amount, 2) }}</td>
        <td>
          <span class="px-2 py-0.5 rounded-full text-xs font-semibold
            {{ $item->order->status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : (in_array($item->order->status, ['Cancelled','Returned']) ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
            {{ $item->order->status }}
          </span>
        </td>
        <td>{{ $item->order->placed_at->format('d M Y') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="mt-4">{{ $items->links() }}</div>
  @endif
</div>
@endsection
