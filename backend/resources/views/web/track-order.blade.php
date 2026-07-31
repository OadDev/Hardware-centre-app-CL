@extends('web.layout')

@section('title', 'Track Your Order · Hardware Centers')
@section('description', 'Track your Hardware Centers order in real time.')

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Track Order</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg> Real-time order tracking</span>
    <h1 style="margin-top:14px;">Track your order</h1>
    <p>Enter your order number and registered phone/email to see live delivery status.</p>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="track-search">
      <div class="track-search-in">
        <h3>Enter your order details</h3>
        <p>You'll find your order number in the confirmation email or under My Orders.</p>
        <form class="track-form" data-track-form="{{ route('api.web.track-order') }}">
          <input type="text" name="order_no" placeholder="Order number, e.g. HC-2026-04821" required />
          <input type="text" name="contact" placeholder="Registered phone or email" required />
          <button type="submit" class="btn btn-primary" style="height:52px;">Track Order</button>
        </form>
        <div data-track-error style="display:none;margin-top:14px;font-size:13px;color:#EF4444;font-weight:600;"></div>
      </div>
    </div>
  </div>
</section>

<section class="section-tight" data-track-result style="display:none;">
  <div class="container" style="max-width:760px;">
    <div class="card" style="padding:30px;">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:26px;">
        <div>
          <div style="font-size:12px;color:var(--muted-2);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Order Number</div>
          <div data-track-order-no style="font-family:'Manrope',sans-serif;font-size:20px;font-weight:800;margin-top:4px;"></div>
        </div>
        <span class="badge badge-orange" data-track-status></span>
      </div>
      <div class="order-item" style="margin-top:0;">
        <div class="thumb"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6"><path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div>
        <div><div class="title" data-track-title></div><div class="sub" data-track-sub></div></div>
      </div>
      <div style="height:1px;background:var(--border-soft);margin:24px 0;"></div>
      <div class="tracker" data-track-timeline></div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container" style="text-align:center;">
    <h3 class="section-title" style="font-size:20px;">Still need help with your order?</h3>
    <p class="section-sub" style="margin:10px auto 0;">Our support team can help with delays, damaged items or address changes.</p>
    <a href="{{ route('web.support') }}" class="btn btn-dark" style="margin-top:18px;">Contact Support</a>
  </div>
</section>
@endsection
