@extends('web.layout')

@section('title', $product->name.' · '.$product->brand->name.' · Hardware Centers')
@section('description', str($product->description)->limit(155))

@section('content')
@php
$off = $product->mrp > 0 ? round((1 - (float) $product->price / (float) $product->mrp) * 100) : 0;
$images = $product->images->pluck('url')->filter()->values();
@endphp
<section class="page-hero" style="padding:28px 0;">
  <div class="container">
    <div class="crumb">
      <a href="{{ route('web.home') }}">Home</a>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      <a href="{{ route('web.search') }}?category={{ $product->category->icon }}">{{ $product->category->name }}</a>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      <span style="color:#fff;">{{ $product->name }}</span>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container pdp-grid">
    <div>
      <div class="pdp-hero" id="pdp-hero">
        @if($images->count())
          <img src="{{ $images->first() }}" alt="{{ $product->name }}" id="pdp-hero-img" />
        @else
          <span>{{ $product->brand->name }}<br>{{ $product->name }}</span>
        @endif
        @if($off > 0)<div class="pdp-off-badge">-{{ $off }}% OFF</div>@endif
      </div>
      @if($images->count() > 1)
      <div class="pdp-thumbs">
        @foreach($images as $i => $url)
        <div class="pdp-thumb {{ $i === 0 ? 'active' : '' }}" data-pdp-thumb="{{ $url }}"><img src="{{ $url }}" alt="" /></div>
        @endforeach
      </div>
      @endif
    </div>

    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div class="pdp-brand">{{ $product->brand->name }}</div>
        <div class="badge badge-green"><svg width="12" height="12" viewBox="0 0 24 24" fill="#F59E0B" stroke="none"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg> {{ number_format($product->rating, 1) }} <span style="opacity:.7;">({{ number_format($product->reviews_count) }})</span></div>
      </div>
      <h1 class="pdp-name">{{ $product->name }}</h1>
      <div class="pdp-price-row">
        <span class="pdp-price">₹{{ number_format($product->price) }}</span>
        @if($off > 0)<span class="pdp-mrp">₹{{ number_format($product->mrp) }}</span><span class="pdp-off">-{{ $off }}%</span>@endif
      </div>
      <div class="pdp-tax-note">Inclusive of all taxes · per {{ $product->unit }}</div>

      @php
        $attributeOrder = ['Color', 'Size'];
        $variants = $product->variants->where('status', 'Active')->values();
        $attributeGroups = collect($attributeOrder)->map(fn ($key) => [
          'key' => $key,
          'values' => $variants->pluck('options.'.$key)->filter()->unique()->values(),
        ])->filter(fn ($g) => $g['values']->count() > 0);
      @endphp
      @foreach($attributeGroups as $group)
      <div class="pdp-variant-group">
        <div class="pdp-variant-label">{{ $group['key'] }}</div>
        <div class="pdp-variant-row">
          @foreach($group['values'] as $value)
          <div class="tab-btn {{ $loop->first ? 'active' : '' }}" data-variant-option="{{ $group['key'] }}:{{ $value }}">{{ $value }}</div>
          @endforeach
        </div>
      </div>
      @endforeach

      <div class="pdp-info-cards">
        <div class="pdp-info-card" data-pdp-stock-card style="background:{{ $product->stock > 0 ? 'var(--green-bg)' : 'var(--red-bg)' }};">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $product->stock > 0 ? '#16A34A' : '#EF4444' }}" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
          <div><div class="t1" data-pdp-stock-title>{{ $variants->isNotEmpty() ? 'Select an option' : $product->stock_status }}</div><div class="t2" data-pdp-stock-sub>{{ $variants->isNotEmpty() ? '' : ($product->stock > 0 ? 'Ready to ship' : 'Currently unavailable') }}</div></div>
        </div>
        <div class="pdp-info-card" style="background:var(--surface);border:1px solid var(--border);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>
          <div><div class="t1">Delivery in {{ $product->eta_days }}–{{ $product->eta_days + 1 }} days</div><div class="t2">to New Delhi</div></div>
        </div>
      </div>

      <div class="pdp-actions">
        <button class="btn btn-outline btn-block" data-pdp-wish="{{ $product->product_code }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2" style="margin-right:6px;vertical-align:-3px;"><path d="M12 21s-7.5-4.6-10-9.1C.4 8.8 1.6 5.5 4.7 5c2-.3 3.6.8 4.3 2 .7-1.2 2.3-2.3 4.3-2 3.1.5 4.3 3.8 2.7 6.9C19.5 16.4 12 21 12 21Z"/></svg>Save</button>
        <button class="btn btn-primary btn-block" data-pdp data-add-to-cart="{{ $product->product_code }}" @if($variants->isEmpty() && $product->stock <= 0) disabled @endif>Add to Cart</button>
      </div>

      @if($variants->isNotEmpty())
      <script type="application/json" id="pdp-variants-data">{!! json_encode($variants->map(fn ($v) => [
        'id' => $v->variant_code,
        'attributes' => $v->options,
        'price' => (float) $v->effective_price,
        'stock' => $v->stock,
      ])->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
      @endif
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container" style="max-width:900px;">
    <div class="section-head"><div class="section-title" style="font-size:20px;">Specifications</div></div>
    <div class="pdp-spec-box">
      @forelse($product->specs as $spec)
      <div class="pdp-spec-row"><div class="lbl">{{ $spec->label }}</div><div class="val">{{ $spec->value }}</div></div>
      @empty
      <div class="pdp-spec-row"><div class="val">No specifications listed for this product.</div></div>
      @endforelse
    </div>

    <div class="section-head" style="margin-top:36px;"><div class="section-title" style="font-size:20px;">Description</div></div>
    <p style="font-size:13.5px;color:#4B5563;line-height:1.7;">{{ $product->description ?: 'No description provided.' }}</p>

    <div class="section-head" style="margin-top:36px;"><div class="section-title" style="font-size:20px;">Sold by</div></div>
    <div class="supplier-row">
      <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.8"><path d="m9 12 2 2 4-4"/><path d="M12 2 3 6v6c0 5 3.8 9.4 9 10 5.2-.6 9-5 9-10V6l-9-4z"/></svg></div>
      <div>
        <div class="name">{{ $product->seller->store_name }} <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg></div>
        <div class="meta">{{ $product->seller->city }} · ⭐ {{ number_format($product->seller->rating, 1) }} · {{ $product->seller->joined_at ? (int) now()->diffInYears($product->seller->joined_at, true) : 0 }} yrs in business</div>
      </div>
    </div>

    <div class="section-head" style="margin-top:36px;"><div class="section-title" style="font-size:20px;">Ratings &amp; Reviews</div></div>
    <div class="pdp-rating-summary">
      <div class="pdp-rating-big">
        <div class="num">{{ number_format($product->rating, 1) }}</div>
        <div class="stars">★★★★★</div>
        <div class="cnt">{{ number_format($product->reviews_count) }} reviews</div>
      </div>
      <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
        @foreach($ratingBreakdown as $bar)
        <div class="pdp-bar-row"><span class="n">{{ $bar['stars'] }}</span><div class="pdp-bar-track"><div class="pdp-bar-fill" style="width:{{ $bar['pct'] }}%;"></div></div></div>
        @endforeach
      </div>
    </div>
    @foreach($reviews as $r)
    <div class="pdp-review">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="pdp-review-avatar">{{ $r['init'] }}</div>
        <div><div class="pdp-review-name">{{ $r['name'] }}</div><div class="pdp-review-meta">★★★★★ · {{ $r['date'] }}</div></div>
      </div>
      <div class="pdp-review-text">{{ $r['text'] }}</div>
    </div>
    @endforeach
  </div>
</section>

@if($related->count())
<section class="section-tight">
  <div class="container">
    <div class="section-head"><div class="section-title" style="font-size:20px;">You may also like</div></div>
    <div class="prod-grid">
      @foreach($related as $p)
        <x-web.product-card :product="$p" />
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
