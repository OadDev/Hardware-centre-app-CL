@extends('seller.layout')

@section('title', 'Warehouse')

@section('content')
<div class="space-y-6">

  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="font-bold text-ink-900 mb-4">Your Warehouse Inventory ({{ $inventory->total() }})</div>
    @if($inventory->isEmpty())
    <p class="text-sm text-ink-400">You don't have any stock in our warehouse yet. Send a shipment below to get started.</p>
    @else
    <table class="w-full text-sm">
      <thead><tr class="text-left text-ink-400 border-b border-ink-100">
        <th class="py-2 font-medium">Product</th><th class="font-medium">Variant</th><th class="font-medium">Warehouse</th><th class="font-medium">Available</th><th class="font-medium">Reserved</th>
      </tr></thead>
      <tbody>
        @foreach($inventory as $row)
        <tr class="border-b border-ink-100 last:border-0">
          <td class="py-2.5">{{ $row->product->name }}</td>
          <td>{{ $row->productVariant?->label ?? '—' }}</td>
          <td>{{ $row->warehouse->name }}</td>
          <td class="font-medium {{ $row->quantity_available <= 10 ? 'text-amber-600' : 'text-ink-800' }}">{{ $row->quantity_available }}</td>
          <td>{{ $row->quantity_reserved }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-4">{{ $inventory->links() }}</div>
    @endif
  </div>

  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="font-bold text-ink-900 mb-4">Send Inventory to Warehouse</div>
    @if($products->isEmpty())
    <p class="text-sm text-ink-400">Add a product first before sending stock to the warehouse.</p>
    @else
    <form method="POST" action="{{ route('seller.warehouse.shipments.store') }}" class="space-y-4">
      @csrf
      <div id="shipment-items" class="space-y-3">
        <div class="grid grid-cols-12 gap-2.5 items-end shipment-item-row">
          <div class="col-span-6">
            <label class="block text-xs font-medium text-ink-500 mb-1">Product</label>
            <select name="items[0][product_code]" class="w-full px-3 py-2 text-sm rounded-xl border border-ink-200" required>
              @foreach($products as $p)
              <option value="{{ $p->product_code }}">{{ $p->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-3">
            <label class="block text-xs font-medium text-ink-500 mb-1">Variant (optional)</label>
            <input name="items[0][variant_code]" placeholder="e.g. VAR-101" class="w-full px-3 py-2 text-sm rounded-xl border border-ink-200">
          </div>
          <div class="col-span-3">
            <label class="block text-xs font-medium text-ink-500 mb-1">Quantity</label>
            <input type="number" min="1" name="items[0][quantity]" class="w-full px-3 py-2 text-sm rounded-xl border border-ink-200" required>
          </div>
        </div>
      </div>
      <button type="button" id="add-shipment-item" class="text-sm font-medium text-brand-600 hover:text-brand-700">+ Add another product</button>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-ink-500 mb-1">Expected arrival (optional)</label>
          <input type="date" name="expected_at" class="w-full px-3 py-2 text-sm rounded-xl border border-ink-200">
        </div>
        <div>
          <label class="block text-xs font-medium text-ink-500 mb-1">Notes (optional)</label>
          <input name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-ink-200">
        </div>
      </div>
      <button type="submit" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl">Create Shipment</button>
    </form>
    @endif
  </div>

  <div class="bg-white rounded-2xl border border-ink-200/70 p-5">
    <div class="font-bold text-ink-900 mb-4">Your Shipments ({{ $shipments->total() }})</div>
    @if($shipments->isEmpty())
    <p class="text-sm text-ink-400">No shipments sent yet.</p>
    @else
    <table class="w-full text-sm">
      <thead><tr class="text-left text-ink-400 border-b border-ink-100">
        <th class="py-2 font-medium">Shipment</th><th class="font-medium">Items</th><th class="font-medium">Status</th><th class="font-medium">Created</th>
      </tr></thead>
      <tbody>
        @foreach($shipments as $s)
        <tr class="border-b border-ink-100 last:border-0">
          <td class="py-2.5">{{ $s->shipment_no }}</td>
          <td>{{ $s->items->sum('quantity_expected') }} units ({{ $s->items->count() }} products)</td>
          <td>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
              {{ $s->status === 'Received' ? 'bg-emerald-50 text-emerald-700' : ($s->status === 'Rejected' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
              {{ $s->status }}
            </span>
          </td>
          <td>{{ $s->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-4">{{ $shipments->links() }}</div>
    @endif
  </div>

</div>

<script>
  let shipmentItemIndex = 1;
  document.getElementById('add-shipment-item')?.addEventListener('click', () => {
    const wrap = document.getElementById('shipment-items');
    const row = wrap.querySelector('.shipment-item-row').cloneNode(true);
    row.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace(/items\[\d+\]/, `items[${shipmentItemIndex}]`);
      if (el.tagName === 'INPUT') el.value = '';
    });
    wrap.appendChild(row);
    shipmentItemIndex++;
  });
</script>
@endsection
