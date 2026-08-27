@extends('web.layout')

@php
$brand = $settings->business_name ?: 'Hardware Centers';
$email = $settings->support_email ?: 'support@hardwarecenters.com';
$phone = $settings->contact_number;
@endphp

@section('title', 'Returns & Refund Policy · '.$brand)
@section('description', 'How returns, replacements and refunds work on '.$brand.'.')

@section('content')
<section class="page-hero">
  <div class="container" style="max-width:780px;">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Returns &amp; Refund Policy</span></div>
    <h1>Returns &amp; Refund Policy</h1>
    <p>Most products on {{ $brand }} can be returned within 7 days of delivery if they arrive damaged, defective, or not as described. Some categories are non-returnable — see below for details.</p>
    <div class="policy-updated">Last updated: {{ now()->format('d M Y') }}</div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:780px;">
    <div class="policy-toc">
      <div class="t">On this page</div>
      <ol>
        <li><a href="#cancellations">Cancelling an order</a></li>
        <li><a href="#eligibility">Return eligibility</a></li>
        <li><a href="#non-returnable">Non-returnable items</a></li>
        <li><a href="#how-to-return">How to request a return</a></li>
        <li><a href="#pickup">Pickup &amp; inspection</a></li>
        <li><a href="#refunds">Refund method &amp; timeline</a></li>
        <li><a href="#replacements">Replacements</a></li>
        <li><a href="#seller-fulfilled">Seller-fulfilled orders</a></li>
        <li><a href="#contact">Need help?</a></li>
      </ol>
    </div>

    <div class="policy-body">
      <h2 id="cancellations">1. Cancelling an order</h2>
      <p>You can cancel an order from the Order History section any time before it has been shipped by the seller, for a full refund of any amount paid. Once an order has shipped, it can no longer be cancelled — you may instead request a return once it's delivered, subject to eligibility below.</p>

      <h2 id="eligibility">2. Return eligibility</h2>
      <p>An item is eligible for return within <strong>7 days of delivery</strong> if:</p>
      <ul>
        <li>It arrived damaged or physically defective.</li>
        <li>It is materially different from what was shown/described on the product page (wrong item, wrong size/spec, missing parts).</li>
        <li>It develops a manufacturing defect covered under the product's stated warranty period (see the product's Specifications and Warranty details).</li>
      </ul>
      <p>To be eligible, the item should be unused, in its original packaging, with all accessories, manuals, and freebies included, wherever practical for the product type. For bulk/wholesale orders, please report any shortfall or damage within 48 hours of delivery so it can be verified against the delivery record.</p>

      <h2 id="non-returnable">3. Non-returnable items</h2>
      <p>Certain categories cannot be returned for reasons of hygiene, safety, or because they are made or cut to order, including:</p>
      <ul>
        <li>Items cut, mixed, or customised to your specification (e.g. paint tinted to a custom shade, pipes/cables cut to length).</li>
        <li>Consumables and items with a sealed hygiene/safety seal once that seal is broken (e.g. adhesives, sealants, safety gear like respirators).</li>
        <li>Products installed or used, unless the issue is a manufacturing defect covered under warranty.</li>
        <li>Products explicitly marked "Non-Returnable" on the product page.</li>
      </ul>

      <h2 id="how-to-return">4. How to request a return</h2>
      <ol>
        <li>Go to <strong>Orders</strong> in your account and select the order you want to return.</li>
        <li>Choose <strong>Request Return</strong>, select a reason, and add photos if the item arrived damaged or incorrect — this speeds up approval.</li>
        <li>You'll receive a confirmation once the seller or our support team reviews the request, usually within 24–48 hours.</li>
      </ol>

      <h2 id="pickup">5. Pickup &amp; inspection</h2>
      <p>For approved returns, we'll arrange a reverse pickup from your delivery address where serviceable; otherwise you may be asked to ship the item back and we'll reimburse reasonable return shipping for damaged/defective/incorrect items. Returned items are inspected on arrival before a refund or replacement is processed.</p>

      <h2 id="refunds">6. Refund method &amp; timeline</h2>
      <ul>
        <li>Refunds for prepaid orders (UPI, card, netbanking) are credited to the original payment method, typically within 5–7 business days of the return being approved after inspection.</li>
        <li>Refunds for Cash on Delivery orders are issued via bank transfer or store credit/wallet, once you share bank details or choose your preferred refund method.</li>
        <li>Delivery charges are refunded only if the return is due to a seller/platform error (damaged, defective, or incorrect item) — not for returns due to a change of mind, where applicable.</li>
        <li>Any coupon discount applied to a returned item is adjusted from the refund amount if returning the item would take the order below the coupon's minimum order value.</li>
      </ul>

      <h2 id="replacements">7. Replacements</h2>
      <p>For damaged, defective, or incorrect items, you can choose a replacement instead of a refund where the seller has the same item in stock. Replacement requests follow the same process as returns above.</p>

      <h2 id="seller-fulfilled">8. Seller-fulfilled orders</h2>
      <p>{{ $brand }} is a marketplace, so returns for marketplace orders are coordinated with the fulfilling seller, but our support team will step in to help resolve any dispute over eligibility, condition, or refund timing. You are never required to negotiate directly with a seller who is unresponsive — contact us and we'll follow up on your behalf.</p>

      <h2 id="contact">9. Need help?</h2>
      <p>For anything not covered here, or to check the status of an existing return, contact our support team:</p>
      <ul>
        <li>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></li>
        @if($phone)<li>Phone: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>@endif
        <li>Or use <a href="{{ route('web.track-order') }}">Track Order</a> / <a href="{{ route('web.support') }}">Help &amp; Support</a> from the site menu.</li>
      </ul>
    </div>
  </div>
</section>
@endsection
