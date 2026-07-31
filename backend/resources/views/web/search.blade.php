@extends('web.layout')

@section('title', 'Search · Hardware Centers')
@section('description', 'Search 8,000+ hardware products from GST-verified suppliers on Hardware Centers.')

@section('content')
<section class="page-hero" style="padding:44px 0 40px;">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Search</span></div>
    <h1 style="font-size:28px;">Search 8,000+ products</h1>
    <div style="max-width:640px;margin-top:20px;display:flex;align-items:center;gap:10px;height:54px;border-radius:14px;padding:0 16px;background:#fff;box-shadow:var(--shadow-pop);">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
      <input data-search-input type="text" placeholder="Try 'impact drill' or 'Havells wire'" style="flex:1;border:none;outline:none;font-size:14.5px;font-family:inherit;color:#111827;" />
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start;">
    <aside>
      <div class="card" style="padding:20px;">
        <div style="font-size:14px;font-weight:800;margin-bottom:6px;">Filter by Brand</div>
        <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">Select one or more brands</div>
        <div class="brand-check-list">
          @foreach($brands as $b)
          <label class="brand-check"><input type="checkbox" data-brand-filter="{{ $b->name }}" /><span class="lbl">{{ $b->name }}</span><span class="cnt">{{ $b->products_count }}</span></label>
          @endforeach
        </div>
      </div>
    </aside>
    <div>
      <div style="margin-bottom:20px;">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px;">Recent Searches</div>
        <div class="chip-row">
          <div class="chip" data-fill-search="impact drill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> impact drill</div>
          <div class="chip" data-fill-search="cpvc pipe"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> cpvc pipe</div>
          <div class="chip" data-fill-search="havells wire"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> havells wire</div>
          <div class="chip" data-fill-search="safety helmet"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> safety helmet</div>
        </div>
      </div>
      <div style="margin-bottom:26px;">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px;">Popular Searches</div>
        <div class="chip-row">
          <div class="chip chip-orange" data-fill-search="Power Tools"><svg width="13" height="13" viewBox="0 0 24 24" fill="#F97316" stroke="none" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> Power Tools</div>
          <div class="chip chip-orange" data-fill-search="PVC Fittings"><svg width="13" height="13" viewBox="0 0 24 24" fill="#F97316" stroke="none" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> PVC Fittings</div>
          <div class="chip chip-orange" data-fill-search="Cement"><svg width="13" height="13" viewBox="0 0 24 24" fill="#F97316" stroke="none" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> Cement</div>
          <div class="chip chip-orange" data-fill-search="Hand Gloves"><svg width="13" height="13" viewBox="0 0 24 24" fill="#F97316" stroke="none" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> Hand Gloves</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div id="search-results-title" style="font-size:18px;font-weight:800;font-family:'Manrope',sans-serif;">Trending Products</div>
        <span data-result-count style="font-size:12.5px;color:var(--muted);">{{ $trending->count() }} products found</span>
      </div>
      <div class="prod-grid" id="search-results">
        @foreach($trending as $p)
          <x-web.product-card :product="$p" />
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection
