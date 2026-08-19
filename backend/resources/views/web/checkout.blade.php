@extends('web.layout')

@section('title', 'Checkout · Hardware Centers')
@section('description', 'Complete your Hardware Centers order.')

@section('content')
<section class="page-hero" style="padding:36px 0;">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <a href="{{ route('web.cart') }}">Cart</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Checkout</span></div>
    <h1 style="font-size:28px;">Checkout</h1>
  </div>
</section>

<section class="section-tight" id="checkout-page">
  <div class="container">

    <div data-checkout-logged-out style="display:none;text-align:center;padding:60px 20px;">
      <p style="font-size:15px;font-weight:700;">Log in to check out</p>
      <p style="font-size:13.5px;color:var(--muted);margin-top:6px;">One quick OTP and you're in — same account as the app.</p>
      <button type="button" class="btn btn-primary" style="margin-top:20px;" data-checkout-login-btn>Login / Sign Up</button>
    </div>

    <div data-checkout-logged-in style="display:none;" class="cart-layout">
      <div>
        <div class="card" style="padding:22px;">
          <div style="font-size:15px;font-weight:800;margin-bottom:14px;">Delivery Address</div>
          <div style="display:flex;flex-direction:column;gap:11px;" data-checkout-addresses></div>

          <div data-checkout-add-address-form style="display:none;margin-top:16px;border-top:1px solid var(--border-soft);padding-top:16px;">
            <div style="font-size:13.5px;font-weight:700;margin-bottom:12px;">Add New Address</div>
            <button type="button" class="btn btn-outline btn-block" data-checkout-use-location style="margin-bottom:16px;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:7px;vertical-align:-3px;"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
              <span data-checkout-use-location-label>Use Current Location</span>
            </button>
            <div class="field-hint" style="margin:-10px 0 16px;">We'll fill in the fields below from your location — you can still edit anything before saving.</div>
            <div class="form-grid">
              <div class="field"><label>Label</label><input type="text" name="label" placeholder="Home / Shop / Warehouse" /></div>
              <div class="field"><label>Contact Name</label><input type="text" name="name" placeholder="Full name" /></div>
              <div class="field full"><label>Address Line</label><input type="text" name="line" placeholder="House no., street, area" /></div>
              <div class="field"><label>City</label><input type="text" name="city" /></div>
              <div class="field">
                <label>State</label>
                <select name="state">
                  <option value="">Select state</option>
                  @foreach($indianStates as $state)
                  <option value="{{ $state }}">{{ $state }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field"><label>Pincode</label><input type="text" name="pincode" /></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
              <button type="button" class="btn btn-outline" data-checkout-cancel-add-form>Cancel</button>
              <button type="button" class="btn btn-primary" data-checkout-save-address>Save Address</button>
            </div>
          </div>
        </div>

        <div class="card" style="padding:22px;margin-top:20px;">
          <div style="font-size:15px;font-weight:800;margin-bottom:14px;">Shipping Method</div>
          <div style="display:flex;flex-direction:column;gap:11px;">
            <div class="addr-card active" data-ship-option="standard">
              <div class="addr-radio active"></div>
              <div style="flex:1;display:flex;justify-content:space-between;align-items:center;">
                <div><div style="font-weight:700;font-size:14px;">Standard Delivery</div><div class="addr-line" style="margin-top:2px;">3–5 business days</div></div>
                <div style="font-weight:800;color:var(--green-dark);font-size:13.5px;">Free</div>
              </div>
            </div>
            <div class="addr-card" data-ship-option="express">
              <div class="addr-radio"></div>
              <div style="flex:1;display:flex;justify-content:space-between;align-items:center;">
                <div><div style="font-weight:700;font-size:14px;">Express Delivery</div><div class="addr-line" style="margin-top:2px;">1–2 business days</div></div>
                <div style="font-weight:800;font-size:13.5px;">₹149</div>
              </div>
            </div>
          </div>
        </div>

        <div class="card" style="padding:0;margin-top:20px;">
          <div style="font-size:15px;font-weight:800;padding:22px 22px 4px;">Payment Method</div>
          <div class="pay-box" style="border:none;">
            <div class="pay-row" data-pay-option="upi">
              <div class="addr-radio active"></div>
              <div><div style="font-weight:700;font-size:14px;">UPI</div><div class="addr-line">GPay, PhonePe, Paytm</div></div>
            </div>
            <div class="pay-row" data-pay-option="card">
              <div class="addr-radio"></div>
              <div><div style="font-weight:700;font-size:14px;">Credit / Debit Card</div><div class="addr-line">Visa, Mastercard, RuPay</div></div>
            </div>
            <div class="pay-row" data-pay-option="netbanking">
              <div class="addr-radio"></div>
              <div><div style="font-weight:700;font-size:14px;">Net Banking</div><div class="addr-line">All major banks</div></div>
            </div>
            <div class="pay-row" data-pay-option="cod">
              <div class="addr-radio"></div>
              <div><div style="font-weight:700;font-size:14px;">Pay on Delivery</div><div class="addr-line">Cash / UPI on arrival</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="cart-summary">
        <div style="font-size:15px;font-weight:800;margin-bottom:16px;">Order Summary</div>
        <div class="cart-coupon-row">
          <input type="text" placeholder="Coupon code" data-co-coupon-input />
        </div>
        <div class="cart-summary-row"><span>Subtotal</span><span data-co-sub>₹0</span></div>
        <div class="cart-summary-row" data-co-disc-row style="display:none;color:var(--green-dark);"><span>Discount</span><span data-co-disc>-₹0</span></div>
        <div class="cart-summary-row"><span>GST</span><span data-co-gst>₹0</span></div>
        <div class="cart-summary-row"><span>Delivery</span><span data-co-deliv>Free</span></div>
        <div class="cart-summary-row" data-co-packaging-row style="display:none;"><span>Packaging</span><span data-co-packaging>₹0</span></div>
        <div class="cart-summary-row" data-co-cod-row style="display:none;"><span>COD Charges</span><span data-co-cod>₹0</span></div>
        <div class="cart-summary-row total"><span>Total Payable</span><span data-co-total>₹0</span></div>
        <button type="button" class="btn btn-primary btn-block" style="margin-top:16px;" data-place-order>Place Order</button>
      </div>
    </div>

  </div>
</section>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush
