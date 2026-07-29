@extends('seller.layout')

@section('title', 'Product Variants')

@section('content')
<a href="{{ route('seller.products') }}" class="text-sm font-medium text-brand-600 mb-4 inline-block">&larr; Back to My Products</a>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900">{{ $product->name }}</div>
  <p class="text-xs text-ink-400 mt-1">Manage color, size, and other options for this product. Each option has its own stock, SKU, and optional price.</p>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900 mb-4">Add an option</div>
  <form method="POST" action="{{ route('seller.products.variants.store', $product) }}" class="grid grid-cols-2 md:grid-cols-5 gap-3">
    @csrf
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Color</label><input name="color" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Size</label><input name="size" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Price override (₹)</label><input type="number" step="0.01" name="price" placeholder="Same as base price" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Stock</label><input type="number" name="stock" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">SKU (optional)</label><input name="sku" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div class="col-span-2 md:col-span-5"><button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Add Option</button></div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Options ({{ $product->variants->count() }})</div>
  @if($product->variants->isEmpty())
  <p class="text-sm text-ink-400">No options yet. Customers will just buy the base product above.</p>
  @else
  <table class="w-full text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium">Option</th><th class="font-medium">SKU</th><th class="font-medium">Price</th><th class="font-medium">Stock</th><th class="font-medium">Status</th><th class="font-medium"></th>
    </tr></thead>
    <tbody>
      @foreach($product->variants as $v)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5 font-medium text-ink-900">{{ $v->label }}</td>
        <td class="text-ink-500">{{ $v->sku }}</td>
        <td>{{ $v->price !== null ? '₹'.number_format($v->price, 2) : 'Base price' }}</td>
        <td>{{ $v->stock }}</td>
        <td>
          <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $v->status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-ink-100 text-ink-500' }}">{{ $v->status }}</span>
        </td>
        <td class="text-right whitespace-nowrap">
          <form method="POST" action="{{ route('seller.products.variants.update', [$product, $v]) }}" class="inline">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $v->status === 'Active' ? 'Inactive' : 'Active' }}">
            <button type="submit" class="text-xs font-semibold text-ink-500 mr-3">{{ $v->status === 'Active' ? 'Deactivate' : 'Activate' }}</button>
          </form>
          <form method="POST" action="{{ route('seller.products.variants.destroy', [$product, $v]) }}" class="inline" onsubmit="return confirm('Remove this option?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs font-semibold text-rose-600">Remove</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>
@endsection
