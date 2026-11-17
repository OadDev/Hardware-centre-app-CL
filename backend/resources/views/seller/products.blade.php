@extends('seller.layout')

@section('title', 'My Products')

@section('content')
<div class="bg-white rounded-2xl border border-ink-200/70 p-5 mb-6">
  <div class="font-bold text-ink-900 mb-4">Add a new product</div>
  <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-3 gap-3">
    @csrf
    <div class="col-span-2 md:col-span-1"><label class="block text-xs font-medium text-ink-500 mb-1">Product Name</label><input name="name" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Category</label>
      <select name="category_code" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200">
        <option value="">Select</option>
        @foreach($categories as $c)
          @if($c->children->isNotEmpty())
          <optgroup label="{{ $c->name }}">
            @foreach($c->children as $sub)<option value="{{ $sub->category_code }}">{{ $sub->name }}</option>@endforeach
          </optgroup>
          @else
          <option value="{{ $c->category_code }}">{{ $c->name }}</option>
          @endif
        @endforeach
      </select>
    </div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Brand</label>
      <select name="brand_code" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200">
        <option value="">Select</option>
        @foreach($brands as $b)<option value="{{ $b->brand_code }}">{{ $b->name }}</option>@endforeach
      </select>
    </div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Price (₹)</label><input type="number" step="0.01" name="price" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">MRP (₹)</label><input type="number" step="0.01" name="mrp" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Stock</label><input type="number" name="stock" required class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Weight per unit (kg) <span class="text-ink-400 font-normal">(optional)</span></label><input type="number" step="0.001" name="weight_kg" placeholder="e.g. 0.5" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div class="col-span-2 md:col-span-3"><label class="block text-xs font-medium text-ink-500 mb-1">Description</label><textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></textarea></div>
    <div class="col-span-2 md:col-span-2">
      <label class="block text-xs font-medium text-ink-500 mb-1">Product Photos (up to 8)</label>
      <input type="file" name="images[]" multiple accept="image/*" class="w-full text-sm text-ink-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 file:text-ink-700 file:text-xs file:font-medium">
    </div>
    <div><label class="block text-xs font-medium text-ink-500 mb-1">Video Link (optional)</label><input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..." class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
    <div class="col-span-2 md:col-span-3"><button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Submit for Approval</button></div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-ink-200/70 p-5">
  <div class="font-bold text-ink-900 mb-4">Your Products ({{ $products->total() }})</div>
  @if($products->isEmpty())
  <p class="text-sm text-ink-400">You haven't listed any products yet.</p>
  @else
  <form method="POST" action="{{ route('seller.products.bulk-action') }}" onsubmit="return window.sellerProductsBulkSubmit(event);">
    @csrf
    <div class="flex flex-wrap items-center gap-2 mb-3 p-2.5 rounded-xl bg-ink-50 border border-ink-100">
      <span class="text-xs font-semibold text-ink-500 mr-1">Bulk actions:</span>
      <select name="action" class="px-2.5 py-1.5 text-xs rounded-lg border border-ink-200">
        <option value="activate">Activate</option>
        <option value="deactivate">Deactivate</option>
        <option value="archive">Archive</option>
        <option value="price">Adjust price by %</option>
        <option value="delete">Delete</option>
      </select>
      <input type="number" step="0.1" name="price_percent" placeholder="e.g. 10 or -5" class="w-28 px-2.5 py-1.5 text-xs rounded-lg border border-ink-200" title="Only used for 'Adjust price by %' — positive to increase, negative to decrease">
      <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-ink-800 text-white">Apply to selected</button>
      <span class="text-xs text-ink-400" id="seller-bulk-selected-count">0 selected</span>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full seller-table-wide text-sm">
      <thead><tr class="text-left text-ink-400 border-b border-ink-100">
        <th class="py-2 font-medium w-8"><input type="checkbox" id="seller-select-all" onclick="window.sellerProductsToggleAll(this)"></th>
        <th class="font-medium"></th><th class="font-medium">Product</th><th class="font-medium">Price</th><th class="font-medium">Stock</th><th class="font-medium">Status</th><th class="font-medium">Approval</th><th class="font-medium"></th>
      </tr></thead>
      <tbody>
        @foreach($products as $p)
        <tr class="border-b border-ink-100 last:border-0">
          <td class="py-2.5"><input type="checkbox" name="ids[]" value="{{ $p->product_code }}" class="seller-product-checkbox" onclick="window.sellerProductsUpdateCount()"></td>
          <td class="py-2.5">
            @if($p->images->first())
              <img src="{{ $p->images->first()->url }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-ink-200">
            @else
              <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-[10px] text-ink-400">No photo</div>
            @endif
          </td>
          <td class="py-2.5">{{ $p->name }}<div class="text-xs text-ink-400">{{ $p->brand->name }} · {{ $p->category->name }}</div>
            @if($p->video_url)<a href="{{ $p->video_url }}" target="_blank" class="text-xs text-brand-600 font-semibold">▶ Watch video</a>@endif
          </td>
          <td>₹{{ number_format($p->price, 2) }}</td>
          <td>{{ $p->stock }}</td>
          <td>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
              {{ $p->status === 'Active' ? 'bg-emerald-50 text-emerald-700' : ($p->status === 'Archived' ? 'bg-ink-100 text-ink-500' : 'bg-amber-50 text-amber-700') }}">
              {{ $p->status }}
            </span>
          </td>
          <td>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
              {{ $p->approval_status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : ($p->approval_status === 'Rejected' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700') }}">
              {{ $p->approval_status }}
            </span>
          </td>
          <td class="whitespace-nowrap">
            <button type="button" class="text-ink-600 text-xs font-semibold mr-3" onclick="document.getElementById('seller-edit-{{ $p->product_code }}').classList.toggle('hidden')">Edit</button>
            <a href="{{ route('seller.products.variants', $p) }}" class="text-brand-600 text-xs font-semibold mr-3">Options{{ $p->variants->count() ? ' ('.$p->variants->count().')' : '' }}</a>
            <button type="button" class="text-rose-600 text-xs font-semibold" onclick="return confirm('Remove this product?') && document.getElementById('seller-destroy-{{ $p->product_code }}').submit();">Remove</button>
          </td>
        </tr>
        <tr id="seller-edit-{{ $p->product_code }}" class="hidden border-b border-ink-100 last:border-0 bg-ink-50/60">
          <td colspan="8" class="p-4">
            <form method="POST" action="{{ route('seller.products.update', $p) }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-4 gap-3">
              @csrf @method('PATCH')
              <div><label class="block text-xs font-medium text-ink-500 mb-1">Product Name</label><input name="name" value="{{ $p->name }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div><label class="block text-xs font-medium text-ink-500 mb-1">Price (₹)</label><input type="number" step="0.01" name="price" value="{{ $p->price }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div><label class="block text-xs font-medium text-ink-500 mb-1">MRP (₹)</label><input type="number" step="0.01" name="mrp" value="{{ $p->mrp }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div><label class="block text-xs font-medium text-ink-500 mb-1">Stock</label><input type="number" name="stock" value="{{ $p->stock }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div><label class="block text-xs font-medium text-ink-500 mb-1">Weight per unit (kg)</label><input type="number" step="0.001" name="weight_kg" value="{{ $p->weight_kg }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div><label class="block text-xs font-medium text-ink-500 mb-1">Video Link</label><input type="url" name="video_url" value="{{ $p->video_url }}" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200"></div>
              <div class="col-span-2 md:col-span-2"><label class="block text-xs font-medium text-ink-500 mb-1">Add more photos (optional)</label><input type="file" name="images[]" multiple accept="image/*" class="w-full text-sm text-ink-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 file:text-ink-700 file:text-xs file:font-medium"></div>
              <div class="col-span-2 md:col-span-4"><label class="block text-xs font-medium text-ink-500 mb-1">Description</label><textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-ink-200">{{ $p->description }}</textarea></div>
              <div class="col-span-2 md:col-span-4"><button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold">Save Changes</button></div>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  </form>
  @foreach($products as $p)
    <form id="seller-destroy-{{ $p->product_code }}" method="POST" action="{{ route('seller.products.destroy', $p) }}" class="hidden">@csrf @method('DELETE')</form>
  @endforeach
  <div class="mt-4">{{ $products->links() }}</div>
  @endif
</div>
<script>
  window.sellerProductsToggleAll = function (master) {
    document.querySelectorAll('.seller-product-checkbox').forEach(cb => cb.checked = master.checked);
    window.sellerProductsUpdateCount();
  };
  window.sellerProductsUpdateCount = function () {
    const n = document.querySelectorAll('.seller-product-checkbox:checked').length;
    const el = document.getElementById('seller-bulk-selected-count');
    if (el) el.textContent = n + ' selected';
  };
  window.sellerProductsBulkSubmit = function (e) {
    const n = document.querySelectorAll('.seller-product-checkbox:checked').length;
    if (n === 0) { alert('Select at least one product first.'); e.preventDefault(); return false; }
    const action = e.target.querySelector('select[name="action"]').value;
    if (action === 'delete' && !confirm('Delete ' + n + ' product(s) permanently?')) { e.preventDefault(); return false; }
    return true;
  };
</script>
@endsection
