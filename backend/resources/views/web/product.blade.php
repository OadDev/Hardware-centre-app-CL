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
        $variants = $product->variants->where('status', 'Active')->values();
        $priceTiers = $product->priceTiers->sortBy('min_qty')->values();
      @endphp
      @if($variants->isEmpty() && $priceTiers->isNotEmpty())
      <div class="pdp-tier-box">
        <div class="pdp-tier-title">Buy more, save more</div>
        <table class="pdp-tier-table">
          <tbody>
            <tr><td>1 – {{ $priceTiers->first()->min_qty - 1 }} {{ $product->unit }}</td><td>₹{{ number_format($product->price) }} / {{ $product->unit }}</td></tr>
            @foreach($priceTiers as $i => $tier)
            <tr><td>{{ $tier->min_qty }}{{ isset($priceTiers[$i + 1]) ? ' – '.($priceTiers[$i + 1]->min_qty - 1) : '+' }} {{ $product->unit }}</td><td>₹{{ number_format($tier->price) }} / {{ $product->unit }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
      <div id="pdp-tier-box-dynamic"></div>

      @if($variants->isNotEmpty())
      <div class="pdp-variant-group">
        <div class="pdp-variant-label">Options</div>
        <div class="pdp-variant-row">
          @foreach($variants as $v)
          <div class="tab-btn" data-variant-option="{{ $v->variant_code }}">{{ $v->label }}</div>
          @endforeach
        </div>
      </div>
      @endif

      <div class="pdp-info-cards">
        <div class="pdp-info-card" data-pdp-stock-card style="background:{{ $product->stock > 0 ? 'var(--green-bg)' : 'var(--red-bg)' }};">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $product->stock > 0 ? '#16A34A' : '#EF4444' }}" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
          <div><div class="t1" data-pdp-stock-title>{{ $variants->isNotEmpty() ? 'Select an option' : $product->stock_status }}</div><div class="t2" data-pdp-stock-sub>{{ $variants->isNotEmpty() ? '' : ($product->stock > 0 ? 'Ready to ship' : 'Currently unavailable') }}</div></div>
        </div>
        <div class="pdp-info-card" style="background:var(--surface);border:1px solid var(--border);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>
          <div><div class="t1">Delivery in {{ $product->eta_days }}–{{ $product->eta_days + 1 }} days</div><div class="t2" data-pdp-delivery-to>to your location</div></div>
        </div>
      </div>

      <div class="pdp-actions">
        <button class="pdp-wish-btn" data-pdp-wish="{{ $product->product_code }}" aria-label="Save"><svg width="19" height="19" viewBox="0 0 24 24" fill="#9CA3AF" stroke="#9CA3AF" stroke-width="1.5"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></button>
        <button class="btn btn-primary" data-pdp data-add-to-cart="{{ $product->product_code }}" @if($variants->isEmpty() && $product->stock <= 0) disabled @endif>Add to Cart</button>
      </div>

      @if($contactNumber)
      @php
        $waDigits = preg_replace('/\D/', '', $contactNumber);
        $waNumber = strlen($waDigits) === 10 ? '91'.$waDigits : $waDigits;
        $waMsg = rawurlencode('Hi, I am interested in '.$product->name.' — '.url()->current());
      @endphp
      <div class="pdp-contact-row">
        <a href="tel:+{{ $waNumber }}" class="pdp-contact-btn">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <span>Buy on Call</span>
        </a>
        <a href="https://wa.me/{{ $waNumber }}?text={{ $waMsg }}" target="_blank" rel="noopener" class="pdp-contact-btn">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="#25D366" stroke="none"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.35-1.4a9.9 9.9 0 0 0 4.69 1.19h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2m0 18.15c-1.47 0-2.9-.39-4.14-1.13l-.3-.18-3.08.81.82-3-.19-.31a8.2 8.2 0 0 1-1.26-4.42c0-4.54 3.7-8.24 8.25-8.24a8.19 8.19 0 0 1 8.24 8.24c0 4.55-3.7 8.24-8.24 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.78.97-.14.17-.29.19-.53.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.87.85-.87 2.08 0 1.22.89 2.4 1.01 2.57.13.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.28"/></svg>
          <span>Buy on Chat</span>
        </a>
      </div>
      @endif

      @if($variants->isNotEmpty())
      <script type="application/json" id="pdp-variants-data">{!! json_encode([
        'unit' => $product->unit,
        'apply_bulk_pricing_to_all_variants' => (bool) $product->apply_bulk_pricing_to_all_variants,
        'product_price_tiers' => $priceTiers->map(fn ($t) => ['min_qty' => $t->min_qty, 'price' => (float) $t->price])->values(),
        'variants' => $variants->map(fn ($v) => [
          'id' => $v->variant_code,
          'label' => $v->label,
          'image' => $v->image_url,
          'price' => (float) $v->effective_price,
          'stock' => $v->stock,
          'price_tiers' => $v->priceTiers->map(fn ($t) => ['min_qty' => $t->min_qty, 'price' => (float) $t->price])->values(),
        ])->values(),
      ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
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
