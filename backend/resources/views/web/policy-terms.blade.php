@extends('web.layout')

@php
$brand = $settings->business_name ?: 'Hardware Centers';
$email = $settings->support_email ?: 'support@hardwarecenters.com';
$phone = $settings->contact_number;
$address = $settings->registered_address;
$gst = $settings->gst_number;
@endphp

@section('title', 'Terms of Service · '.$brand)
@section('description', 'The terms that govern your use of '.$brand.' as a buyer or seller.')

@section('content')
<section class="page-hero">
  <div class="container" style="max-width:780px;">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Terms of Service</span></div>
    <h1>Terms of Service</h1>
    <p>These terms govern your access to and use of {{ $brand }} as a buyer or a registered seller. Please read them carefully before placing an order or listing a product.</p>
    <div class="policy-updated">Last updated: {{ now()->format('d M Y') }}</div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:780px;">
    <div class="policy-toc">
      <div class="t">On this page</div>
      <ol>
        <li><a href="#acceptance">Acceptance of terms</a></li>
        <li><a href="#accounts">Accounts &amp; eligibility</a></li>
        <li><a href="#marketplace">The marketplace model</a></li>
        <li><a href="#orders-pricing">Orders, pricing &amp; GST</a></li>
        <li><a href="#payments">Payments</a></li>
        <li><a href="#shipping">Shipping &amp; delivery</a></li>
        <li><a href="#returns">Cancellations, returns &amp; refunds</a></li>
        <li><a href="#seller-obligations">Seller obligations</a></li>
        <li><a href="#prohibited">Prohibited use</a></li>
        <li><a href="#ip">Intellectual property</a></li>
        <li><a href="#liability">Limitation of liability</a></li>
        <li><a href="#termination">Suspension &amp; termination</a></li>
        <li><a href="#governing-law">Governing law &amp; disputes</a></li>
        <li><a href="#changes">Changes to these terms</a></li>
        <li><a href="#contact">Contact us</a></li>
      </ol>
    </div>

    <div class="policy-body">
      <h2 id="acceptance">1. Acceptance of terms</h2>
      <p>By creating an account, browsing, or placing an order on {{ $brand }} (the "Platform"), you agree to be bound by these Terms of Service and our <a href="{{ route('web.policy.privacy') }}">Privacy Policy</a>. If you are using the Platform on behalf of a business, you confirm you have authority to bind that business to these terms.</p>

      <h2 id="accounts">2. Accounts &amp; eligibility</h2>
      <ul>
        <li>You must be at least 18 years old and capable of entering into a binding contract under Indian law to use the Platform.</li>
        <li>Accounts are verified using a one-time password (OTP) sent to your registered phone number or email. You are responsible for keeping your login credentials confidential.</li>
        <li>You agree to provide accurate, current information when registering, including business name and GSTIN where applicable, and to keep it updated.</li>
      </ul>

      <h2 id="marketplace">3. The marketplace model</h2>
      <p>{{ $brand }} is an online marketplace that connects buyers with independent, GST-verified sellers. Each product listing is created and fulfilled by the seller named on that listing — {{ $brand }} facilitates discovery, ordering, and payment, but the contract of sale for each order is between the buyer and the seller. {{ $brand }} is not the manufacturer or, unless explicitly stated, the seller of record for marketplace listings.</p>

      <h2 id="orders-pricing">4. Orders, pricing &amp; GST</h2>
      <ul>
        <li>Prices shown on a product page are exclusive of GST unless stated otherwise; the applicable GST rate (based on the product's HSN code) is shown separately and added at checkout.</li>
        <li>Placing an order is an offer to purchase, which is accepted once the order is confirmed and payment is processed (or, for Cash on Delivery, once the order is confirmed).</li>
        <li>Bulk/quantity pricing tiers, where shown, apply automatically once the ordered quantity meets the stated threshold, subject to available stock.</li>
        <li>We reserve the right to cancel an order if a listed price or stock level was incorrect due to a technical error, in which case any amount already paid will be refunded in full.</li>
      </ul>

      <h2 id="payments">5. Payments</h2>
      <p>We support UPI, credit/debit card, netbanking, and Cash on Delivery (where enabled for a given order). Online payments are processed by a third-party payment gateway; a Cash on Delivery handling fee may apply where indicated at checkout. Coupon codes are subject to their stated minimum order value and validity period.</p>

      <h2 id="shipping">6. Shipping &amp; delivery</h2>
      <p>Delivery charges and estimated delivery times depend on the delivery state, shipping method selected (Standard or Express), order weight, and order value, as shown at checkout. Delivery estimates are indicative and not guaranteed delivery dates; delays may occur due to logistics, weather, or circumstances beyond our control.</p>

      <h2 id="returns">7. Cancellations, returns &amp; refunds</h2>
      <p>Full details are set out in our <a href="{{ route('web.policy.returns') }}">Returns &amp; Refund Policy</a>. In summary: orders can typically be cancelled before they are shipped, and eligible items can be returned within the stated return window if damaged, defective, or materially different from what was ordered.</p>

      <h2 id="seller-obligations">8. Seller obligations</h2>
      <p>Registered sellers agree to:</p>
      <ul>
        <li>List only genuine products they are authorised to sell, with accurate descriptions, pricing, and stock levels.</li>
        <li>Hold a valid GST registration where required by law and issue compliant tax invoices for orders fulfilled.</li>
        <li>Pack and dispatch orders promptly, and honour the price and terms shown at the time an order was placed.</li>
        <li>Respond to buyer queries and return/replacement requests in a timely manner.</li>
      </ul>
      <p>{{ $brand }} may suspend or remove listings, or suspend a seller account, for repeated quality issues, inaccurate listings, or violation of these terms.</p>

      <h2 id="prohibited">9. Prohibited use</h2>
      <ul>
        <li>Listing counterfeit, stolen, or illegal goods.</li>
        <li>Attempting to circumvent the Platform's payment or fee systems.</li>
        <li>Using automated tools to scrape, spam, or interfere with the Platform's normal operation.</li>
        <li>Posting false reviews or manipulating ratings.</li>
        <li>Impersonating another person or business, or providing false verification documents.</li>
      </ul>

      <h2 id="ip">10. Intellectual property</h2>
      <p>The {{ $brand }} name, logo, and website/app design are the property of {{ $brand }}. Product images and descriptions belong to the respective sellers or brands. Nothing in these terms grants you rights to use our branding without prior written permission.</p>

      <h2 id="liability">11. Limitation of liability</h2>
      <p>To the extent permitted by law, {{ $brand }} is not liable for indirect, incidental, or consequential damages arising from use of the Platform, delays in delivery caused by third-party logistics providers, or the quality of products supplied by independent sellers beyond facilitating returns/refunds under our stated policy. Nothing in these terms limits liability that cannot be excluded under applicable Indian consumer protection law.</p>

      <h2 id="termination">12. Suspension &amp; termination</h2>
      <p>We may suspend or terminate access to the Platform for accounts that violate these terms, engage in fraudulent activity, or pose a risk to other users. You may close your account at any time by contacting support; open orders will still be fulfilled or refunded per our Returns &amp; Refund Policy.</p>

      <h2 id="governing-law">13. Governing law &amp; disputes</h2>
      <p>These terms are governed by the laws of India. Any disputes arising from use of the Platform will be subject to the exclusive jurisdiction of the courts having jurisdiction over our registered address{{ $address ? ' ('.$address.')' : '' }}.</p>

      <h2 id="changes">14. Changes to these terms</h2>
      <p>We may update these Terms of Service from time to time. The "Last updated" date above reflects the latest revision. Continued use of the Platform after changes take effect constitutes acceptance of the updated terms.</p>

      <h2 id="contact">15. Contact us</h2>
      <ul>
        <li>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></li>
        @if($phone)<li>Phone: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>@endif
        @if($gst)<li>GSTIN: {{ $gst }}</li>@endif
        @if($address)<li>Registered address: {{ $address }}</li>@endif
      </ul>
    </div>
  </div>
</section>
@endsection
