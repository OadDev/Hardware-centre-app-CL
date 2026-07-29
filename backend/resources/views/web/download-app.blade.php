@extends('web.layout')

@section('title', 'Download the App · Hardware Centre')
@section('description', 'Shop 8,000+ hardware products, track orders, and get app-only deals on the Hardware Centre app for iOS and Android.')

@section('content')
<section class="section" style="padding-top:56px;">
  <div class="container app-hero">
    <div>
      <span class="eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="1.8"><path d="M12 3v12"/><path d="m6 11 6 6 6-6"/><path d="M4 21h16"/></svg> Now on iOS &amp; Android</span>
      <h2 class="section-title" style="font-size:38px;margin-top:16px;">Shop hardware on the go</h2>
      <p class="section-sub" style="font-size:16px;">Browse 8,000+ products, track orders in real time, and get exclusive app-only deals — all from your phone.</p>
      <div class="hero-cta" style="margin-top:26px;">
        <a href="#" class="app-badge" style="background:#111827;"><svg width="22" height="22" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="0"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.02 7.31c1.34.07 2.28.74 3.06.8.94-.16 2.03-.83 3.06-.7 1.87.15 3.03.9 3.76 2.1-3.3 2-2.72 6.13.63 7.51-.53 1.26-1.22 2.5-1.98 3.26zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.31-2.08 4.5-3.74 4.25z"/></svg><span><span class="txt-a" style="color:#9CA3AF;">Download on the</span><span class="txt-b">App Store</span></span></a>
        <a href="#" class="app-badge" style="background:#111827;"><svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="1.4"><path d="m5 3 14 9-14 9V3z"/></svg><span><span class="txt-a" style="color:#9CA3AF;">Get it on</span><span class="txt-b">Google Play</span></span></a>
      </div>
      <div class="qr-card">
        <div class="qr-box"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.3"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM19 19h2v2h-2zM14 19h2M19 14h2"/></svg></div>
        <div><div style="font-size:14px;font-weight:700;">Scan to download</div><div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Point your camera at the QR code to get the app instantly.</div></div>
      </div>
    </div>
    <div class="phone-mock"><div class="screen">
      <div style="height:34px;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:0 18px;color:#fff;font-size:11px;font-weight:600;">
        <span>9:41</span>
        <span style="display:flex;gap:4px;align-items:center;"><svg width="12" height="9" viewBox="0 0 24 24" fill="#fff" stroke="none" stroke-width="0"><rect x="0" y="7" width="3" height="5" rx="1"/><rect x="5" y="4.5" width="3" height="7.5" rx="1"/><rect x="10" y="2" width="3" height="10" rx="1"/></svg></span>
      </div>
      <div style="flex:1;background:#fff;border-radius:20px 20px 0 0;padding:16px 14px;display:flex;flex-direction:column;gap:14px;overflow:hidden;">
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="width:30px;height:30px;border-radius:9px;background:#1F2937;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-family:'Manrope',sans-serif;font-weight:800;font-size:11px;">K</span></div>
          <div style="flex:1;"><div style="font-size:9px;color:#6B7280;">Good morning,</div><div style="font-size:11.5px;font-weight:700;color:#111827;">Kumar Hardware</div></div>
          <div style="width:26px;height:26px;border-radius:8px;background:#F8FAFC;"></div>
        </div>
        <div style="height:34px;border-radius:10px;background:#F8FAFC;border:1px solid #E5E7EB;display:flex;align-items:center;padding:0 10px;gap:6px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg><span style="font-size:9.5px;color:#9CA3AF;">Search tools, brands…</span></div>
        <div style="height:70px;border-radius:12px;background:linear-gradient(120deg,#1F2937,#374151);position:relative;overflow:hidden;padding:12px;">
          <div style="font-size:8px;color:#FDBA74;font-weight:800;letter-spacing:.5px;">MEGA SALE</div>
          <div style="font-size:12px;color:#fff;font-weight:800;margin-top:4px;">Up to 40% off Power Tools</div>
        </div>
        <div style="display:flex;gap:10px;">
          @foreach(['electrical', 'plumbing', 'paint', 'tools', 'fasteners'] as $ic)
          <div style="display:flex;flex-direction:column;align-items:center;gap:4px;"><div style="width:34px;height:34px;border-radius:10px;background:#F8FAFC;border:1px solid #EEF1F4;display:flex;align-items:center;justify-content:center;"><x-web.cat-icon :icon="$ic" size="14" /></div><span style="font-size:7px;font-weight:700;color:#374151;">{{ ucfirst($ic) }}</span></div>
          @endforeach
        </div>
        <div style="display:flex;gap:8px;">
          <div style="flex:1;border:1px solid #EEF1F4;border-radius:10px;padding:6px;"><div style="height:36px;border-radius:6px;background:#F8FAFC;margin-bottom:5px;"></div><div style="font-size:7px;color:#9CA3AF;">Bosch</div><div style="font-size:8px;font-weight:800;color:#111827;">₹4,299</div></div>
          <div style="flex:1;border:1px solid #EEF1F4;border-radius:10px;padding:6px;"><div style="height:36px;border-radius:6px;background:#F8FAFC;margin-bottom:5px;"></div><div style="font-size:7px;color:#9CA3AF;">Havells</div><div style="font-size:8px;font-weight:800;color:#111827;">₹2,150</div></div>
        </div>
      </div>
      <div style="height:54px;background:#fff;border-top:1px solid #EEF1F4;display:flex;align-items:center;justify-content:space-around;">
        <div style="width:16px;height:16px;border-radius:4px;background:#F97316;"></div><div style="width:16px;height:16px;border-radius:4px;background:#E5E7EB;"></div><div style="width:16px;height:16px;border-radius:4px;background:#E5E7EB;"></div><div style="width:16px;height:16px;border-radius:4px;background:#E5E7EB;"></div><div style="width:16px;height:16px;border-radius:4px;background:#E5E7EB;"></div>
      </div>
    </div></div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container">
    <div class="section-head center"><span class="eyebrow">App features</span><h2 class="section-title" style="margin-top:14px;">Built for busy buyers</h2></div>
    <div class="benefit-grid">
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div><h4>Live order tracking</h4><p>Follow every order from confirmation to delivery with real-time push notifications.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 21s-7.5-4.6-10-9.1C.4 8.8 1.6 5.5 4.7 5c2-.3 3.6.8 4.3 2 .7-1.2 2.3-2.3 4.3-2 3.1.5 4.3 3.8 2.7 6.9C19.5 16.4 12 21 12 21Z"/></svg></div><h4>Wishlist &amp; reorder</h4><p>Save frequently bought items and reorder your usual supplies in two taps.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></div><h4>App-only deals</h4><p>Unlock exclusive discounts and early access to Deals of the Day.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></div><h4>Price drop alerts</h4><p>Get notified the moment a wishlisted product's price falls.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 5v6c0 5.5 3.4 9 8 11 4.6-2 8-5.5 8-11V5z"/></svg></div><h4>Secure payments</h4><p>UPI, cards and business credit — all encrypted and PCI-DSS compliant.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg></div><h4>Seller tools on mobile</h4><p>Verified sellers can manage listings and orders directly from the app.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band">
      <div class="cta-band-in">
        <div><h3>Get the Hardware Centre app today</h3><p>Available free on the App Store and Google Play.</p></div>
        <div class="btns">
          <a href="#" class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="0"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.02 7.31c1.34.07 2.28.74 3.06.8.94-.16 2.03-.83 3.06-.7 1.87.15 3.03.9 3.76 2.1-3.3 2-2.72 6.13.63 7.51-.53 1.26-1.22 2.5-1.98 3.26zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.31-2.08 4.5-3.74 4.25z"/></svg> App Store</a>
          <a href="#" class="btn btn-outline-light"><svg width="14" height="14" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="1.4"><path d="m5 3 14 9-14 9V3z"/></svg> Google Play</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
