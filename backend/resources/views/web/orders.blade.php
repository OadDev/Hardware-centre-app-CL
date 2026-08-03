@extends('web.layout')

@section('title', 'My Orders · Hardware Centers')
@section('description', 'View your Hardware Centers order history and track shipments.')

@section('content')
<section class="page-hero" style="padding:44px 0 34px;">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">My Orders</span></div>
    <h1 style="font-size:28px;">Order History</h1>
    <p>Your Hardware Centers order history, synced with your account.</p>
  </div>
</section>

<section class="section-tight" data-my-orders-real style="display:none;">
  <div class="container" style="max-width:720px;">
    <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
      <button type="button" class="hc-modal-link" style="margin:0;" data-my-orders-login-toggle>Look up an order by email/phone instead</button>
    </div>
    <div data-my-orders-real-list style="display:flex;flex-direction:column;gap:14px;"></div>
  </div>
</section>

<section class="section-tight" data-my-orders-guest>
  <div class="container" style="max-width:560px;">
    <div class="card" style="padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;">
      <p style="font-size:13.5px;color:var(--muted);margin:0;">Have an account? Log in to see your full order history automatically.</p>
      <button type="button" class="btn btn-outline btn-sm" data-my-orders-login-btn>Login</button>
    </div>
    <div class="card" style="padding:24px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
      <div class="field" style="flex:1;min-width:220px;margin:0;"><label>Email or phone used at checkout</label><input type="text" data-my-orders-input placeholder="rajesh@kumarhardware.in" /></div>
      <button type="button" class="btn btn-primary" data-my-orders-submit="{{ route('api.web.my-orders') }}" style="height:48px;">Find My Orders</button>
    </div>
    <div data-my-orders-error style="display:none;margin-top:12px;font-size:13px;color:#EF4444;font-weight:600;"></div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);padding-top:8px;display:none;" data-my-orders-results>
  <div class="container" style="padding-top:24px;">
    <div class="tab-row" data-order-tabs>
      <button class="tab-btn active" data-order-tab="all">All Orders</button>
      <button class="tab-btn" data-order-tab="ongoing">Ongoing</button>
      <button class="tab-btn" data-order-tab="Delivered">Delivered</button>
      <button class="tab-btn" data-order-tab="Cancelled">Cancelled</button>
    </div>
  </div>
  <div class="container" style="max-width:720px;margin-top:22px;">
    <div style="display:flex;flex-direction:column;gap:14px;" data-my-orders-list></div>
    <div data-my-orders-empty style="display:none;padding:40px 0;text-align:center;color:var(--muted);">No orders found for this contact.</div>
  </div>
</section>
@endsection
