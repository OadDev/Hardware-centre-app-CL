@extends('web.layout')

@section('title', 'Home · Hardware Centers')
@section('description', "Hardware Centers — India's B2B hardware marketplace. Shop 8,000+ products across electrical, plumbing, paint, tools, construction and more from GST-verified suppliers.")

@section('content')
<section class="hero">
  <div class="container">
    <div>
      <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="#FDBA74" stroke="#FDBA74" stroke-width="0" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> India's B2B Hardware Marketplace</span>
      <h1>Everything Hardware.<br><span class="accent">One Marketplace.</span></h1>
      <p>8,000+ products across 12 categories from GST-verified suppliers — tools, electrical, plumbing, paint, construction &amp; more, delivered to 500+ cities.</p>
      <div class="hero-cta">
        <a href="{{ route('web.categories') }}" class="btn btn-primary">Start Shopping</a>
        <a href="{{ route('web.sell') }}" class="btn btn-outline-light">Become a Seller</a>
      </div>
      <div class="hero-stats">
        <div><div class="n">8,000+</div><div class="l">Products listed</div></div>
        <div><div class="n">1,200+</div><div class="l">Verified suppliers</div></div>
        <div><div class="n">500+</div><div class="l">Cities served</div></div>
        <div><div class="n">4.7★</div><div class="l">Buyer rating</div></div>
      </div>
    </div>
    <div class="hero-art">
      <div class="hero-panel">
        <div class="hero-search-card">
          <div class="row"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg> Search tools, brands, suppliers…</div>
          <div class="hero-chip-row">
            <span class="hero-chip">Power Tools</span>
            <span class="hero-chip">PVC Fittings</span>
            <span class="hero-chip">Cement</span>
          </div>
        </div>
      </div>
      <div class="hero-float-card" style="top:-18px; right:10px;">
        <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="#22C55E" stroke="none" stroke-width="0"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m8.5 12 2.3 2.3 4.7-4.8" stroke="#fff" stroke-width="2" fill="none"/></svg></div>
        <div><div class="t1">GST Verified</div><div class="t2">1,200+ suppliers</div></div>
      </div>
      <div class="hero-float-card" style="bottom:-18px; left:0;">
        <div class="ic" style="background:#FFF7ED;"><svg width="18" height="18" viewBox="0 0 24 24" fill="#F97316" stroke="none" stroke-width="0"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div>
        <div><div class="t1">Fast Delivery</div><div class="t2">500+ cities</div></div>
      </div>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="display:flex;flex-wrap:wrap;gap:28px;justify-content:space-between;padding:8px 24px;background:var(--surface);border:1px solid var(--border-soft);border-radius:var(--radius-lg);">
    <div style="display:flex;align-items:center;gap:10px;"><span style="width:38px;height:38px;border-radius:11px;background:#fff;border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 5v6c0 5.5 3.4 9 8 11 4.6-2 8-5.5 8-11V5z"/></svg></span><span style="font-size:13.5px;font-weight:700;">GST Verified Suppliers</span></div>
    <div style="display:flex;align-items:center;gap:10px;"><span style="width:38px;height:38px;border-radius:11px;background:#fff;border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></span><span style="font-size:13.5px;font-weight:700;">Pan-India Delivery</span></div>
    <div style="display:flex;align-items:center;gap:10px;"><span style="width:38px;height:38px;border-radius:11px;background:#fff;border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></span><span style="font-size:13.5px;font-weight:700;">Best Trade Prices</span></div>
    <div style="display:flex;align-items:center;gap:10px;"><span style="width:38px;height:38px;border-radius:11px;background:#fff;border:1px solid var(--border-soft);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span><span style="font-size:13.5px;font-weight:700;">24×7 Support</span></div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head" style="display:flex;align-items:flex-end;justify-content:space-between;max-width:none;">
      <div><span class="eyebrow">Browse</span><h2 class="section-title" style="margin-top:12px;">Shop by Category</h2></div>
      <a href="{{ route('web.categories') }}" class="link-arrow">See all categories <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
    </div>
    <div class="cat-row">
      @foreach($navCategories as $cat)
      <a class="cat-pill" href="{{ route('web.categories') }}#{{ $cat->icon }}">
        <div class="ic"><x-web.cat-icon :icon="$cat->icon" size="23" /></div>
        <span>{{ $cat->name }}</span>
      </a>
      @endforeach
      <a class="cat-pill" href="{{ route('web.categories') }}#more">
        <div class="ic"><x-web.cat-icon icon="more" size="23" /></div>
        <span>More</span>
      </a>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="section-head" style="display:flex;align-items:flex-end;justify-content:space-between;max-width:none;">
      <div><h2 class="section-title" style="font-size:24px;">Popular Products</h2></div>
      <a href="{{ route('web.search') }}" class="link-arrow">See all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
    </div>
    <div class="prod-row">
      @foreach($popular as $p)
        <x-web.product-card :product="$p" width="184px" />
      @endforeach
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <h2 class="section-title" style="font-size:24px;margin-bottom:20px;">Featured Brands</h2>
    <div class="brand-row">
      @foreach($brands as $b)
      <a class="brand-tile" href="{{ route('web.search') }}?brand={{ urlencode($b->name) }}">
        <div class="ic">
          @if($b->logo_url)
          <img src="{{ $b->logo_url }}" alt="{{ $b->name }}">
          @else
          <span>{{ collect(explode(' ', $b->name))->map(fn($w) => $w[0])->join('') }}</span>
          @endif
        </div>
        <span class="lbl">{{ $b->name }}</span>
      </a>
      @endforeach
    </div>
  </div>
</section>

@if($deals->count())
<section class="section-tight">
  <div class="container">
    <div class="deal-strip">
      <div class="deal-head">
        <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.9"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> Deals of the Day</h3>
        <div class="deal-timer" data-countdown><span class="box">06</span><span class="box">24</span><span class="box">51</span></div>
      </div>
      <div class="prod-row">
        @foreach($deals as $p)
          <x-web.product-card :product="$p" width="170px" />
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif

<section class="section-tight">
  <div class="container">
    <div class="section-head" style="display:flex;align-items:flex-end;justify-content:space-between;max-width:none;">
      <div><h2 class="section-title" style="font-size:24px;">Trending Now</h2></div>
      <a href="{{ route('web.search') }}" class="link-arrow">See all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
    </div>
    <div class="prod-grid">
      @foreach($trending as $p)
        <x-web.product-card :product="$p" />
      @endforeach
    </div>
  </div>
</section>

@if($suppliers->count())
<section class="section-tight">
  <div class="container" style="max-width:800px;">
    <div class="section-head" style="display:flex;align-items:flex-end;justify-content:space-between;max-width:none;">
      <div><h2 class="section-title" style="font-size:24px;">Top Suppliers</h2></div>
      <a href="{{ route('web.categories') }}" class="link-arrow">See all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
    </div>
    <div class="supplier-list">
      @foreach($suppliers as $s)
      <a class="supplier-row" href="{{ route('web.categories') }}">
        <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.6"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg></div>
        <div>
          <div class="name">{{ $s->store_name }} <svg width="15" height="15" viewBox="0 0 24 24" fill="#22C55E" stroke="none" stroke-width="0"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m8.5 12 2.3 2.3 4.7-4.8" stroke="#fff" stroke-width="2" fill="none"/></svg></div>
          <div class="meta">{{ $s->city }} · {{ $s->joined_at ? (int) now()->diffInYears($s->joined_at, true) : 0 }} yrs · GST Verified</div>
        </div>
        <div class="score"><svg width="12" height="12" viewBox="0 0 24 24" fill="#22C55E" stroke="none" stroke-width="0"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg><span>{{ number_format($s->rating, 1) }}</span></div>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="section">
  <div class="container two-col-cta">
    <div class="cta-band" style="padding:36px;">
      <div class="cta-band-in" style="flex-direction:column;align-items:flex-start;">
        <h3 style="font-size:21px;">Grow your business with Hardware Centers</h3>
        <p>Join 1,200+ verified sellers reaching buyers across India.</p>
        <div class="btns" style="margin-top:18px;"><a href="{{ route('web.sell') }}" class="btn btn-primary">Become a Seller</a></div>
      </div>
    </div>
    <div class="cta-band" style="padding:36px;background:#111827;">
      <div class="cta-band-in" style="flex-direction:column;align-items:flex-start;">
        <h3 style="font-size:21px;">Shop faster on the Hardware Centers app</h3>
        <p>Track orders, scan barcodes, and get exclusive app-only deals.</p>
        <div class="btns" style="margin-top:18px;"><a href="{{ route('web.download-app') }}" class="btn btn-outline-light">Download the App</a></div>
      </div>
    </div>
  </div>
</section>
@endsection
