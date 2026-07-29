@extends('seller.layout')

@section('title', 'Settlements')

@section('content')
<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Settlements ({{ $settlements->total() }})</div>
  @if($settlements->isEmpty())
  <p class="text-sm text-ink-400">No settlements recorded yet.</p>
  @else
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Settlement No.</th><th class="font-medium">Amount</th><th class="font-medium">Mode</th><th class="font-medium">Status</th><th class="font-medium">UTR</th><th class="font-medium">Settled</th>
    </tr></thead>
    <tbody>
      @foreach($settlements as $s)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">{{ $s->settlement_no }}</td>
        <td>₹{{ number_format($s->amount, 2) }}</td>
        <td>{{ $s->mode }}</td>
        <td>
          <span class="px-2 py-0.5 rounded-full text-xs font-semibold
            {{ $s->status === 'Paid' ? 'bg-emerald-50 text-emerald-700' : ($s->status === 'Failed' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
            {{ $s->status }}
          </span>
        </td>
        <td>{{ $s->utr ?? '—' }}</td>
        <td>{{ $s->settled_at?->format('d M Y') ?? '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="mt-4">{{ $settlements->links() }}</div>
  @endif
</div>
@endsection
