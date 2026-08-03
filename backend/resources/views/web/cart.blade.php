@extends('web.layout')

@section('title', 'Your Cart · Hardware Centers')
@section('description', 'Review the items in your Hardware Centers cart before checkout.')

@section('content')
<section class="page-hero" style="padding:36px 0;">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Cart</span></div>
    <h1 style="font-size:28px;">Your Cart</h1>
  </div>
</section>

<section class="section-tight" id="cart-page">
  <div class="container">

    <div data-cart-logged-out style="display:none;text-align:center;padding:60px 20px;">
      <p style="font-size:15px;font-weight:700;">Log in to view your cart</p>
      <p style="font-size:13.5px;color:var(--muted);margin-top:6px;">Your cart is tied to your Hardware Centers account, same as the app.</p>
      <button type="button" class="btn btn-primary" style="margin-top:20px;" data-cart-login-btn>Login / Sign Up</button>
    </div>

    <div data-cart-logged-in style="display:none;">
      <div class="cart-empty" data-cart-empty style="display:none;">
        <p style="font-size:15px;font-weight:700;color:var(--ink);">Your cart is empty</p>
        <p style="margin-top:6px;">Browse the catalog and add something you need.</p>
        <a href="{{ route('web.categories') }}" class="btn btn-primary" style="margin-top:20px;">Browse Categories</a>
      </div>

      <div class="cart-layout" data-cart-layout style="display:none;">
        <div class="card" style="padding:20px 22px;">
          <div data-cart-items></div>
        </div>
        <div class="cart-summary">
          <div style="font-size:15px;font-weight:800;margin-bottom:16px;">Order Summary</div>
          <div class="cart-coupon-row">
            <input type="text" placeholder="Coupon code" data-cart-coupon-input />
            <button type="button" class="btn btn-outline btn-sm" data-cart-coupon-apply>Apply</button>
          </div>
          <div data-cart-coupon-msg style="font-size:12px;font-weight:600;margin:-8px 0 14px;display:none;"></div>
          <div class="cart-summary-row"><span>Subtotal</span><span data-cart-sub>₹0</span></div>
          <div class="cart-summary-row" data-cart-disc-row style="display:none;color:var(--green-dark);"><span>Discount</span><span data-cart-disc>-₹0</span></div>
          <div class="cart-summary-row"><span>GST</span><span data-cart-gst>₹0</span></div>
          <div class="cart-summary-row"><span>Delivery</span><span data-cart-deliv>Free</span></div>
          <div class="cart-summary-row total"><span>Total</span><span data-cart-total>₹0</span></div>
          <a href="{{ route('web.checkout') }}" class="btn btn-primary btn-block" style="margin-top:16px;">Proceed to Checkout</a>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
