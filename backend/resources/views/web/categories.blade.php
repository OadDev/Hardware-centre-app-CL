@extends('web.layout')

@section('title', 'Browse by Category · Hardware Centre')
@section('description', 'Browse Hardware Centre\'s 12 categories and 8,000+ products from verified suppliers.')

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Categories</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg> 8,000+ products · {{ $categories->count() }} categories</span>
    <h1 style="margin-top:14px;">Browse by Category</h1>
    <p>Select a category to explore its subcategories and top-rated products from verified suppliers.</p>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="cat-grid">
      @foreach($categories as $i => $cat)
      <div class="cat-card {{ $i === 0 ? 'is-active' : '' }}" id="{{ $cat->icon }}" data-catcard="{{ $cat->icon }}">
        <div class="ic"><x-web.cat-icon :icon="$cat->icon" size="26" /></div>
        <div class="name">{{ $cat->name }}</div>
        <div class="count">{{ number_format($cat->products_count) }} items</div>
      </div>
      @endforeach
      <div class="cat-card dark" id="more" data-catcard="more">
        <div class="ic"><x-web.cat-icon icon="more" size="26" /></div>
        <div class="name">More</div>
        <div class="count">View all</div>
      </div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container" id="subcat-panel-anchor">
    @foreach($categories as $i => $cat)
    <div class="subcat-panel" data-subpanel="{{ $cat->icon }}" @if($i !== 0) style="display:none;" @endif>
      <div class="subcat-head">
        <div class="ic"><x-web.cat-icon :icon="$cat->icon" size="24" /></div>
        <div><div style="font-size:19px;font-weight:800;font-family:'Manrope',sans-serif;">{{ $cat->name }}</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">{{ number_format($cat->products_count) }} items · Verified suppliers · Best trade prices</div></div>
      </div>
      <div class="subcat-grid">
        @foreach($cat->children as $sub)
        <a href="{{ route('web.search') }}?category={{ $sub->icon }}" class="subcat-chip">{{ $sub->name }} <span style="color:#9CA3AF;">({{ number_format($sub->products_count) }})</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a>
        @endforeach
      </div>
      @if($cat->products->count())
      <div style="margin-top:26px;">
        <div style="font-size:15px;font-weight:800;margin-bottom:14px;">Popular in {{ $cat->name }}</div>
        <div class="prod-grid">
          @foreach($cat->products as $p)
            <x-web.product-card :product="$p" />
          @endforeach
        </div>
      </div>
      @endif
    </div>
    @endforeach
    <div class="subcat-panel" data-subpanel="more" style="display:none;">
      <div class="subcat-head">
        <div class="ic"><x-web.cat-icon icon="more" size="24" /></div>
        <div><div style="font-size:19px;font-weight:800;font-family:'Manrope',sans-serif;">More Categories</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Browse everything Hardware Centre has to offer</div></div>
      </div>
      <p style="font-size:13.5px;color:var(--muted);margin-top:10px;">Use <a href="{{ route('web.search') }}" style="color:var(--orange-dark);font-weight:700;">Search</a> to explore the full catalog across all categories.</p>
    </div>
  </div>
</section>
@endsection
