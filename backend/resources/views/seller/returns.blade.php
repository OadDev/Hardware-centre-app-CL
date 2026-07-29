@extends('seller.layout')

@section('title', 'Returns & Refunds')

@section('content')
<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Returns ({{ $returns->total() }})</div>
  @if($returns->isEmpty())
  <p class="text-sm text-ink-400">No returns filed against your products.</p>
  @else
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Return No.</th><th class="font-medium">Order</th><th class="font-medium">Product</th><th class="font-medium">Type</th><th class="font-medium">Amount</th><th class="font-medium">Status</th>
    </tr></thead>
    <tbody>
      @foreach($returns as $r)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">{{ $r->return_no }}</td>
        <td>{{ $r->order->order_no }}</td>
        <td>{{ $r->orderItem?->product?->name ?? '—' }}</td>
        <td>{{ $r->type }}</td>
        <td>₹{{ number_format($r->amount, 2) }}</td>
        <td>
          <span class="px-2 py-0.5 rounded-full text-xs font-semibold
            {{ $r->status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : ($r->status === 'Rejected' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
            {{ $r->status }}
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="mt-4">{{ $returns->links() }}</div>
  @endif
</div>
@endsection
