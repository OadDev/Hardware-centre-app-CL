@extends('seller.layout')

@section('title', 'Product Variants')

@section('content')
<a href="{{ route('seller.products') }}" class="text-sm font-medium text-brand-600 mb-4 inline-block">&larr; Back to My Products</a>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900">{{ $product->name }}</div>
  <p class="text-xs text-ink-400 mt-1">Manage variants for this product — each one gets its own price, stock, SKU, and optional photo.</p>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900 mb-4">Add a variant</div>
  <form method="POST" action="{{ route('seller.products.variants.store', $product) }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-5 gap-3">
    @csrf
    <div class="col-span-2"><label class="block text-xs font-medium text-ink-500 mb-1">Variant name</label><input name="name" required placeholder="e.g. Black, Red, 3mm" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Price override (₹)</label><input type="number" step="0.01" name="price" placeholder="Same as base price" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Stock</label><input type="number" name="stock" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">SKU (optional)</label><input name="sku" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div class="col-span-2 md:col-span-4"><label class="block text-xs font-medium text-ink-500 mb-1">Photo (optional)</label><input type="file" name="image" accept="image/*" class="w-full text-sm text-ink-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 file:text-ink-700 file:text-xs file:font-medium"></div>
    <div class="flex items-end"><button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Add Variant</button></div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Variants ({{ $product->variants->count() }})</div>
  @if($product->variants->isEmpty())
  <p class="text-sm text-ink-400">No variants yet. Customers will just buy the base product above.</p>
  @else
  <div class="overflow-x-auto">
  <table class="w-full seller-table-wide text-sm">
    <thead><tr class="text-left text-ink-400 border-b border-ink-100">
      <th class="py-2 font-medium"></th><th class="font-medium">Variant</th><th class="font-medium">SKU</th><th class="font-medium">Price</th><th class="font-medium">Stock</th><th class="font-medium">Status</th><th class="font-medium"></th>
    </tr></thead>
    <tbody>
      @foreach($product->variants as $v)
      <tr class="border-b border-ink-100 last:border-0">
        <td class="py-2.5">
          @if($v->image_url)
          <img src="{{ $v->image_url }}" class="w-9 h-9 rounded-lg object-cover border border-ink-200">
          @else
          <div class="w-9 h-9 rounded-lg bg-ink-50 border border-ink-200"></div>
          @endif
        </td>
        <td class="font-medium text-ink-900">{{ $v->label }}</td>
        <td class="text-ink-500">{{ $v->sku }}</td>
        <td>{{ $v->price !== null ? '₹'.number_format($v->price, 2) : 'Base price' }}</td>
        <td>{{ $v->stock }}</td>
        <td>
          <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $v->status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-ink-100 text-ink-500' }}">{{ $v->status }}</span>
        </td>
        <td class="text-right whitespace-nowrap">
          <button type="button" data-toggle-edit="edit-row-{{ $v->id }}" class="text-xs font-semibold text-brand-600 mr-3">Edit</button>
          <form method="POST" action="{{ route('seller.products.variants.update', [$product, $v]) }}" class="inline">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $v->status === 'Active' ? 'Inactive' : 'Active' }}">
            <button type="submit" class="text-xs font-semibold text-ink-500 mr-3">{{ $v->status === 'Active' ? 'Deactivate' : 'Activate' }}</button>
          </form>
          <form method="POST" action="{{ route('seller.products.variants.destroy', [$product, $v]) }}" class="inline" onsubmit="return confirm('Remove this variant?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs font-semibold text-rose-600">Remove</button>
          </form>
        </td>
      </tr>
      <tr id="edit-row-{{ $v->id }}" class="hidden border-b border-ink-100 last:border-0">
        <td colspan="7" class="py-3 bg-ink-50/60">
          <form method="POST" action="{{ route('seller.products.variants.update', [$product, $v]) }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @csrf @method('PATCH')
            <div class="col-span-2"><label class="block text-xs font-medium text-ink-500 mb-1">Variant name</label><input name="name" value="{{ $v->label }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
            <div><label class="block text-xs font-medium text-ink-500 mb-1">Price override (₹)</label><input type="number" step="0.01" name="price" value="{{ $v->price }}" placeholder="Same as base price" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
            <div><label class="block text-xs font-medium text-ink-500 mb-1">Stock</label><input type="number" name="stock" value="{{ $v->stock }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
            <div><label class="block text-xs font-medium text-ink-500 mb-1">SKU</label><input name="sku" value="{{ $v->sku }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
            <div class="col-span-2 md:col-span-4"><label class="block text-xs font-medium text-ink-500 mb-1">Replace photo (optional)</label><input type="file" name="image" accept="image/*" class="w-full text-sm text-ink-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 file:text-ink-700 file:text-xs file:font-medium"></div>
            @if($v->image_url)
            <label class="flex items-center gap-2 text-xs text-ink-500"><input type="checkbox" name="remove_image" value="1"> Remove current photo</label>
            @endif
            <div class="col-span-2 md:col-span-5"><button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Save Changes</button></div>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mt-6">
  <div class="font-bold text-ink-900">Bulk / Quantity Pricing</div>
  <p class="text-xs text-ink-400 mt-1 mb-4">Offer a lower unit price when a customer buys a larger quantity, e.g. ₹500/pc under 100 pcs, ₹450/pc from 100 pcs onward. Pick which variant (or the base product) each tier applies to.</p>

  @if($product->variants->isNotEmpty())
  <form method="POST" action="{{ route('seller.products.update', $product) }}" class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-ink-200 mb-5">
    @csrf @method('PATCH')
    <input type="hidden" name="apply_bulk_pricing_to_all_variants" value="{{ $product->apply_bulk_pricing_to_all_variants ? '0' : '1' }}">
    <span class="text-xs font-medium text-ink-600">Also apply base-product tiers to every variant that has no tiers of its own</span>
    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ $product->apply_bulk_pricing_to_all_variants ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-600' }}">{{ $product->apply_bulk_pricing_to_all_variants ? 'On' : 'Off' }}</button>
  </form>
  @endif

  <form method="POST" action="{{ route('seller.products.price-tiers.store', $product) }}" class="grid grid-cols-2 {{ $product->variants->isNotEmpty() ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-3 mb-5">
    @csrf
    @if($product->variants->isNotEmpty())
    <div class="col-span-2 md:col-span-1">
      <label class="block text-xs font-medium text-ink-500 mb-1">Applies to</label>
      <select name="variant_id" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200">
        <option value="">Base product</option>
        @foreach($product->variants as $v)
        <option value="{{ $v->variant_code }}">{{ $v->label }}</option>
        @endforeach
      </select>
    </div>
    @endif
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Minimum quantity</label><input type="number" name="min_qty" min="1" required placeholder="e.g. 100" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Price per unit (₹)</label><input type="number" step="0.01" name="price" min="0" required placeholder="e.g. 450" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div class="flex items-end"><button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Add Tier</button></div>
  </form>

  @php
    $allTiers = $product->allPriceTiers()->with('variant')->get();
    $baseTiers = $allTiers->whereNull('product_variant_id')->sortBy('min_qty');
    $tiersByVariant = $allTiers->whereNotNull('product_variant_id')->groupBy('product_variant_id');
  @endphp
  @if($allTiers->isEmpty())
  <p class="text-sm text-ink-400">No quantity tiers yet — every order pays the base/variant price.</p>
  @else
  <div class="space-y-5">
    @if($baseTiers->isNotEmpty())
    <div>
      <div class="text-xs font-semibold text-ink-500 mb-2">Base product</div>
      <div class="overflow-x-auto"><table class="w-full seller-table-wide text-sm">
        <tbody>
          @foreach($baseTiers as $tier)
          <tr class="border-b border-ink-100 last:border-0">
            <td class="py-2 font-medium text-ink-900">{{ $tier->min_qty }}+ pcs</td>
            <td>₹{{ number_format($tier->price, 2) }}</td>
            <td class="text-right">
              <form method="POST" action="{{ route('seller.products.price-tiers.destroy', [$product, $tier]) }}" class="inline" onsubmit="return confirm('Remove this price tier?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-rose-600">Remove</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table></div>
    </div>
    @endif
    @foreach($product->variants as $v)
      @php($vTiers = ($tiersByVariant[$v->id] ?? collect())->sortBy('min_qty'))
      @if($vTiers->isNotEmpty())
      <div>
        <div class="text-xs font-semibold text-ink-500 mb-2">{{ $v->label }}</div>
        <div class="overflow-x-auto"><table class="w-full seller-table-wide text-sm">
          <tbody>
            @foreach($vTiers as $tier)
            <tr class="border-b border-ink-100 last:border-0">
              <td class="py-2 font-medium text-ink-900">{{ $tier->min_qty }}+ pcs</td>
              <td>₹{{ number_format($tier->price, 2) }}</td>
              <td class="text-right">
                <form method="POST" action="{{ route('seller.products.price-tiers.destroy', [$product, $tier]) }}" class="inline" onsubmit="return confirm('Remove this price tier?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-xs font-semibold text-rose-600">Remove</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table></div>
      </div>
      @endif
    @endforeach
  </div>
  @endif
</div>

<script>
document.querySelectorAll('[data-toggle-edit]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById(btn.dataset.toggleEdit)?.classList.toggle('hidden');
  });
});
</script>
@endsection
