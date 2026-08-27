@extends('web.layout')

@php
$brand = $settings->business_name ?: 'Hardware Centers';
$email = $settings->support_email ?: 'support@hardwarecenters.com';
$phone = $settings->contact_number;
@endphp

@section('title', 'Shipping Policy · '.$brand)
@section('description', 'How delivery charges, timelines and Cash on Delivery work on '.$brand.'.')

@section('content')
<section class="page-hero">
  <div class="container" style="max-width:780px;">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Shipping Policy</span></div>
    <h1>Shipping Policy</h1>
    <p>How delivery charges are calculated, what Standard and Express delivery mean, and what to expect for Cash on Delivery orders.</p>
    <div class="policy-updated">Last updated: {{ now()->format('d M Y') }}</div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:780px;">
    <div class="policy-body">
      <h2>1. Delivery methods</h2>
      <p>At checkout you can choose between two delivery methods, where available for your delivery address:</p>
      <ul>
        <li><strong>Standard Delivery</strong> — our regular delivery speed, typically the most economical option.</li>
        <li><strong>Express Delivery</strong> — a faster delivery speed for an additional charge, where serviceable.</li>
      </ul>
      <p>Both the delivery charge and the estimated delivery window are calculated at checkout based on your delivery state, the method chosen, the total weight of your order, and your order value — some orders qualify for free or discounted delivery above a minimum order value.</p>

      <h2>2. Delivery charges</h2>
      <p>Delivery charges are calculated per order based on the destination state and method selected, and shown clearly before you pay — there are no hidden delivery fees added after checkout. A packaging fee may apply to certain heavy or bulky items, also shown at checkout before payment.</p>

      <h2>3. Delivery timelines</h2>
      <p>Estimated delivery windows shown at checkout and on your order confirmation are our best estimate based on the seller's location and your delivery address, and are not a guaranteed delivery date. Actual delivery may occasionally be affected by weather, regional logistics disruptions, or public holidays.</p>

      <h2>4. Cash on Delivery (COD)</h2>
      <p>Where Cash on Delivery is offered for your order, a COD handling fee may apply and is shown at checkout before you confirm the order. Please have the exact order amount ready for our delivery partner where possible.</p>

      <h2>5. Order tracking</h2>
      <p>Once your order ships, you can track its status from <a href="{{ route('web.orders') }}">My Orders</a> or the <a href="{{ route('web.track-order') }}">Track Order</a> page using your order number.</p>

      <h2>6. Multiple sellers, multiple shipments</h2>
      <p>If your cart includes products from more than one seller, your order may arrive in separate shipments with independent tracking, since each seller ships from their own location. Delivery charges shown at checkout already account for this.</p>

      <h2>7. Delivery issues</h2>
      <p>If your order is significantly delayed, arrives damaged in transit, or a delivery attempt fails, contact our support team and we'll investigate with the delivery partner or seller on your behalf. Damaged-in-transit items are handled under our <a href="{{ route('web.policy.returns') }}">Returns &amp; Refund Policy</a>.</p>

      <h2>8. Contact us</h2>
      <ul>
        <li>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></li>
        @if($phone)<li>Phone: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>@endif
      </ul>
    </div>
  </div>
</section>
@endsection
