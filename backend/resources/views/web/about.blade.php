@extends('web.layout')

@section('title', 'About Us · Hardware Centers')
@section('description', "Hardware Centers connects builders, contractors and retailers with GST-verified suppliers across India.")

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">About Us</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="#FDBA74" stroke="#FDBA74" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> Our Story</span>
    <h1 style="margin-top:14px;">Built to make hardware sourcing simple</h1>
    <p>Hardware Centers connects builders, contractors and retailers with GST-verified suppliers across India — one marketplace for tools, electrical, plumbing, paint and construction materials.</p>
  </div>
</section>

<section class="section-tight">
  <div class="container"><div class="stat-strip">
    <div class="stat-box"><div class="n accent">2019</div><div class="l">Founded in New Delhi</div></div>
    <div class="stat-box"><div class="n">{{ number_format($supplierCount) }}+</div><div class="l">Verified suppliers</div></div>
    <div class="stat-box"><div class="n">{{ number_format($productCount) }}+</div><div class="l">Products listed</div></div>
    <div class="stat-box"><div class="n">500+</div><div class="l">Cities served</div></div>
  </div></div>
</section>

<section class="section">
  <div class="container two-col-split">
    <div class="ph-box" style="height:340px;background:linear-gradient(135deg,#1F2937,#374151);"><span style="color:rgba(255,255,255,.7);">Warehouse &amp; supplier network photo</span></div>
    <div>
      <span class="eyebrow">Why we started</span>
      <h2 class="section-title" style="margin-top:14px;">From a single hardware store to a nationwide marketplace</h2>
      <p class="section-sub">Hardware Centers began with a simple frustration: sourcing quality tools and materials meant calling a dozen suppliers, comparing prices by phone, and hoping the invoice matched what showed up. We built a platform where every supplier is GST-verified, every price is transparent, and every order is trackable end-to-end.</p>
      <p class="section-sub">Today, contractors, retailers and facility teams in 500+ cities use Hardware Centers to source everything from CPVC pipes to power tools — often with same-week delivery.</p>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container">
    <div class="section-head center"><span class="eyebrow">What drives us</span><h2 class="section-title" style="margin-top:14px;">Our values</h2></div>
    <div class="benefit-grid">
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 5v6c0 5.5 3.4 9 8 11 4.6-2 8-5.5 8-11V5z"/></svg></div><h4>Trust first</h4><p>Every supplier is GST-verified and rated by real buyers before they can list a single product.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></div><h4>Fair pricing</h4><p>Transparent trade pricing with no hidden markups — what you see is what you pay.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div><h4>Reliable delivery</h4><p>A logistics network built for bulky, heavy hardware — not just parcels.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div><h4>Real support</h4><p>A human support team that understands hardware, not a generic call center script.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg></div><h4>Seller success</h4><p>We grow when our sellers grow — with tools built for their business, not just ours.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg></div><h4>Nationwide reach</h4><p>From metros to tier-3 towns, we're closing the gap between suppliers and buyers.</p></div>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:680px;">
    <div class="section-head"><span class="eyebrow">Milestones</span><h2 class="section-title" style="margin-top:14px;">Our journey so far</h2></div>
    <div class="tracker">
      <div class="tstep done"><div class="dot-col"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><div class="bar"></div></div>
        <div class="content"><div class="title">2019 — Founded in New Delhi</div><div class="time">Started as a single-city marketplace connecting local hardware retailers with contractors.</div></div></div>
      <div class="tstep done"><div class="dot-col"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><div class="bar"></div></div>
        <div class="content"><div class="title">2021 — Crossed 100 verified suppliers</div><div class="time">Expanded GST verification and quality checks across 8 states.</div></div></div>
      <div class="tstep done"><div class="dot-col"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><div class="bar"></div></div>
        <div class="content"><div class="title">2023 — Launched the mobile app</div><div class="time">Brought order tracking, wishlist and bulk ordering to iOS and Android.</div></div></div>
      <div class="tstep done"><div class="dot-col"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="content"><div class="title">2026 — 500+ cities, 8,000+ products</div><div class="time">Now one of India's largest B2B hardware marketplaces.</div></div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band">
      <div class="cta-band-in">
        <div><h3>Ready to source smarter?</h3><p>Join thousands of buyers and sellers already using Hardware Centers.</p></div>
        <div class="btns"><a href="{{ route('web.categories') }}" class="btn btn-primary">Start Shopping</a><a href="{{ route('web.sell') }}" class="btn btn-outline-light">Become a Seller</a></div>
      </div>
    </div>
  </div>
</section>
@endsection
