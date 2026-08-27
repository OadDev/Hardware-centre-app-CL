<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'Hardware Centers')</title>
<meta name="description" content="@yield('description', "Hardware Centers — India's B2B hardware marketplace. Shop 8,000+ products across electrical, plumbing, paint, tools, construction and more from GST-verified suppliers.")" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('web/img/hc-logo.png') }}" />
<link rel="stylesheet" href="{{ asset('web/css/style.css') }}?v={{ @filemtime(public_path('web/css/style.css')) ?: '1' }}" />
@stack('styles')
</head>
<body>
<div class="topbar">
  <div class="container">
    <button type="button" class="topbar-left topbar-deliver" data-deliver-trigger><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg> Deliver to <b data-deliver-label style="color:#fff;">Set delivery location</b> <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
    <div class="topbar-links">
      <a href="{{ route('web.track-order') }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg> Track Order</a>
      <a href="{{ route('web.sell') }}" class="accent"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg> Become a Seller</a>
      <a href="{{ route('seller.login') }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Seller Login</a>
      <a href="#" data-account-trigger><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg> <span data-account-label>Login / Sign Up</span></a>
      <a href="{{ route('web.download-app') }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m6 11 6 6 6-6"/><path d="M4 21h16"/></svg> Get the App</a>
      <a href="{{ route('web.support') }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg> Help &amp; Support</a>
    </div>
  </div>
</div>
<header class="site-header">
  <div class="container">
    <a href="{{ route('web.home') }}" class="brand">
      <img src="{{ asset('web/img/hc-logo.png') }}" alt="Hardware Centers logo" />
      <span class="brand-name">Hardware <span>Centers</span></span>
    </a>
    <nav><ul class="main-nav">
      <li class="{{ request()->routeIs('web.home') ? 'active' : '' }}"><a href="{{ route('web.home') }}">Home</a></li>
      <li class="has-mega">
        <a href="{{ route('web.categories') }}">Categories <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></a>
        <div class="mega">
          @foreach($navCategories->take(9) as $cat)
          <div class="mega-col">
            <div class="mega-col-title"><span class="ic"><x-web.cat-icon :icon="$cat->icon" size="15" /></span>{{ $cat->name }}</div>
            @foreach($cat->children as $sub)
            <a href="{{ route('web.search') }}?category={{ $sub->icon }}">{{ $sub->name }}</a>
            @endforeach
          </div>
          @endforeach
          <div class="mega-foot"><span style="font-size:12.5px;color:#6B7280;">{{ $navCategories->count() }} categories · 8,000+ products</span><a href="{{ route('web.categories') }}" class="link-arrow">Browse all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a></div>
        </div>
      </li>
      <li class="{{ request()->routeIs('web.search') ? 'active' : '' }}"><a href="{{ route('web.search') }}">Search</a></li>
      <li class="{{ request()->routeIs('web.blog*') ? 'active' : '' }}"><a href="{{ route('web.blog') }}">Blog</a></li>
      <li class="{{ request()->routeIs('web.about') ? 'active' : '' }}"><a href="{{ route('web.about') }}">About Us</a></li>
      <li class="{{ request()->routeIs('web.contact') ? 'active' : '' }}"><a href="{{ route('web.contact') }}">Contact Us</a></li>
    </ul></nav>
    <div class="header-search">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
      <input type="text" placeholder="Search tools, brands, suppliers…" onclick="window.location.href='{{ route('web.search') }}'" readonly />
    </div>
    <div class="header-actions">
      <a class="icon-btn" href="{{ route('web.search') }}" aria-label="Search"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg></a>
      <a class="icon-btn icon-btn-keep" href="{{ route('web.cart') }}" aria-label="Cart"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.4"/><circle cx="19" cy="21" r="1.4"/><path d="M2.5 3h2l2.3 11.4a2 2 0 0 0 2 1.6h8.4a2 2 0 0 0 2-1.6L21 7H6"/></svg><span class="icon-dot" data-cart-badge style="display:none;"></span></a>
      <a class="icon-btn" href="{{ route('web.orders') }}" aria-label="Orders"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></a>
      <a class="btn btn-primary btn-sm" href="{{ route('web.sell') }}">Become a Seller</a>
      <button class="mobile-toggle" aria-label="Menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
    </div>
  </div>
</header>
<div class="mobile-nav">
  <div class="backdrop"></div>
  <div class="panel">
    <button class="close-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    <a href="#" data-account-trigger><span data-account-label>Login / Sign Up</span></a>
    <a href="{{ route('web.home') }}">Home</a>
    <a href="{{ route('web.categories') }}">Categories</a>
    <a href="{{ route('web.search') }}">Search</a>
    <a href="{{ route('web.cart') }}">Cart <span data-cart-badge-text></span></a>
    <a href="{{ route('web.blog') }}">Blog</a>
    <a href="{{ route('web.about') }}">About Us</a>
    <a href="{{ route('web.contact') }}">Contact Us</a>
    <a href="{{ route('web.track-order') }}">Track Order</a>
    <a href="{{ route('web.sell') }}">Become a Seller</a>
    <a href="{{ route('web.download-app') }}">Download App</a>
    <a href="{{ route('web.orders') }}">My Orders</a>
    <a href="{{ route('web.support') }}">Help &amp; Support</a>
  </div>
</div>
<main>
@yield('content')
</main>
<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-brand">
      <a href="{{ route('web.home') }}" class="brand"><img src="{{ asset('web/img/hc-logo.png') }}" alt="Hardware Centers logo" style="width:40px;height:40px;border-radius:11px;" /><span class="brand-name">Hardware <span style="color:#F97316;">Centers</span></span></a>
      <p>Everything hardware, one marketplace. 8,000+ products from GST-verified suppliers, delivered to 500+ cities across India.</p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg></a>
        <a href="#" aria-label="LinkedIn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="4"/><path d="M7 10v7M7 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 13v4"/></svg></a>
        <a href="#" aria-label="YouTube"><svg width="14" height="14" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 3 14 9-14 9V3z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="{{ route('web.about') }}">About Us</a></li>
        <li><a href="{{ route('web.blog') }}">Blog</a></li>
        <li><a href="{{ route('web.sell') }}">Become a Seller</a></li>
        <li><a href="{{ route('web.contact') }}">Contact Us</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Customer Care</h4>
      <ul>
        <li><a href="{{ route('web.support') }}">Help &amp; Support</a></li>
        <li><a href="{{ route('web.track-order') }}">Track Order</a></li>
        <li><a href="{{ route('web.orders') }}">Order History</a></li>
        <li><a href="{{ route('web.policy.returns') }}">Returns &amp; Refunds</a></li>
        <li><a href="{{ route('web.policy.shipping') }}">Shipping Policy</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Top Categories</h4>
      <ul>
        @foreach($navCategories->take(6) as $cat)
        <li><a href="{{ route('web.categories') }}#{{ $cat->icon }}">{{ $cat->name }}</a></li>
        @endforeach
      </ul>
    </div>
    <div class="footer-col">
      <h4>Get the App</h4>
      <div class="footer-app-badges">
        <a href="{{ route('web.download-app') }}" class="app-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="0" stroke-linecap="round" stroke-linejoin="round"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.02 7.31c1.34.07 2.28.74 3.06.8.94-.16 2.03-.83 3.06-.7 1.87.15 3.03.9 3.76 2.1-3.3 2-2.72 6.13.63 7.51-.53 1.26-1.22 2.5-1.98 3.26zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.31-2.08 4.5-3.74 4.25z"/></svg><span><span class="txt-a">Download on the</span><span class="txt-b">App Store</span></span></a>
        <a href="{{ route('web.download-app') }}" class="app-badge"><svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 3 14 9-14 9V3z"/></svg><span><span class="txt-a">Get it on</span><span class="txt-b">Google Play</span></span></a>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© {{ date('Y') }} Hardware Centers. All rights reserved.</span>
    <div class="footer-legal"><a href="{{ route('web.policy.privacy') }}">Privacy Policy</a><a href="{{ route('web.policy.terms') }}">Terms of Service</a><a href="{{ route('web.contact') }}">Sitemap</a></div>
  </div>
</footer>
<div class="toast" data-toast><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> <span data-toast-text>Thanks! We'll be in touch shortly.</span></div>
<script src="{{ asset('web/js/main.js') }}?v={{ @filemtime(public_path('web/js/main.js')) ?: '1' }}"></script>
@stack('scripts')
</body>
</html>
