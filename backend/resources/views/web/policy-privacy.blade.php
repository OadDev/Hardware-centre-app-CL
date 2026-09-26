@extends('web.layout')

@php
$brand = $settings->business_name ?: 'Hardware Centers';
$email = $settings->support_email ?: 'support@hardwarecenters.com';
$phone = $settings->contact_number;
$address = $settings->registered_address;
@endphp

@section('title', 'Privacy Policy · '.$brand)
@section('description', 'How '.$brand.' collects, uses and protects your personal information.')

@section('content')
<section class="page-hero">
  <div class="container" style="max-width:780px;">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Privacy Policy</span></div>
    <h1>Privacy Policy</h1>
    <p>This policy explains what personal information {{ $brand }} collects when you use our website and app, how we use it, and the choices you have.</p>
    <div class="policy-updated">Last updated: {{ now()->format('d M Y') }}</div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:780px;">
    <div class="policy-toc">
      <div class="t">On this page</div>
      <ol>
        <li><a href="#information-we-collect">Information we collect</a></li>
        <li><a href="#how-we-use-it">How we use your information</a></li>
        <li><a href="#sharing">Sharing with sellers &amp; partners</a></li>
        <li><a href="#payments">Payments &amp; financial data</a></li>
        <li><a href="#cookies">Cookies &amp; tracking</a></li>
        <li><a href="#data-retention">Data retention</a></li>
        <li><a href="#your-rights">Your rights &amp; choices</a></li>
        <li><a href="#security">Security</a></li>
        <li><a href="#children">Children's privacy</a></li>
        <li><a href="#changes">Changes to this policy</a></li>
        <li><a href="#contact">Contact us</a></li>
      </ol>
    </div>

    <div class="policy-body">
      <p>{{ $brand }} ("we", "us", "our") operates a B2B hardware marketplace connecting buyers with GST-verified suppliers across India. This Privacy Policy applies to the {{ $brand }} website, mobile app, seller panel and any related services (together, the "Platform"). By using the Platform, you agree to the collection and use of information as described here.</p>

      <h2 id="information-we-collect">1. Information we collect</h2>
      <p>We collect information in three ways: what you give us directly, what we collect automatically as you use the Platform, and what we receive from third parties (like payment gateways or delivery partners).</p>
      <h3>Information you provide</h3>
      <ul>
        <li>Account details — name, phone number, email address, business/company name, GSTIN (optional), delivery addresses.</li>
        <li>Order details — items purchased, quantities, delivery instructions, payment method chosen.</li>
        <li>Seller details (for supplier accounts) — business registration, GSTIN, bank/settlement details, product catalog and pricing.</li>
        <li>Communications — messages sent through Buy on Call, Buy on Chat (WhatsApp), support tickets, or product reviews you post.</li>
      </ul>
      <h3>Information collected automatically</h3>
      <ul>
        <li>Device and usage data — IP address, browser type, device identifiers, pages viewed, and approximate location for delivery estimation.</li>
        <li>Order and browsing history, wishlist activity, and cart contents used to personalise recommendations.</li>
      </ul>

      <h2 id="how-we-use-it">2. How we use your information</h2>
      <ul>
        <li>To create and manage your account, and to process and deliver your orders.</li>
        <li>To verify identity and prevent fraud, including OTP-based login and order verification.</li>
        <li>To generate GST-compliant invoices and maintain records required under Indian tax law.</li>
        <li>To provide customer support, respond to enquiries, and send order/delivery updates via SMS, email, or push notification.</li>
        <li>To improve the Platform — for example, understanding which products and categories are most useful to buyers.</li>
        <li>To send offers, coupons, or product updates where you have not opted out.</li>
      </ul>

      <h2 id="sharing">3. Sharing with sellers &amp; partners</h2>
      <p>Because {{ $brand }} is a marketplace, some information is necessarily shared to fulfil your order:</p>
      <ul>
        <li>The seller fulfilling your order receives your name, delivery address, phone number, and order details needed to ship the product.</li>
        <li>Logistics and delivery partners receive the information needed to pick up and deliver your order.</li>
        <li>Payment gateways (for UPI, card and netbanking payments) receive the information needed to process the transaction; we do not store your full card number.</li>
        <li>We do not sell your personal information to third parties for their own marketing purposes.</li>
      </ul>

      <h2 id="payments">4. Payments &amp; financial data</h2>
      <p>Online payments are processed by our payment gateway partner under their own security standards (PCI-DSS compliant). {{ $brand }} does not store your full card, UPI PIN, or netbanking credentials on its own servers. For Cash on Delivery orders, applicable COD handling charges are collected by the delivery partner at the time of delivery.</p>

      <h2 id="cookies">5. Cookies &amp; tracking</h2>
      <p>We use cookies and similar technologies to keep you signed in, remember your cart, and understand how the Platform is used so we can improve it. You can control cookies through your browser settings, though disabling them may affect features like staying logged in or your cart persisting between visits.</p>

      <h2 id="data-retention">6. Data retention</h2>
      <p>We retain account and order information for as long as your account is active and for a reasonable period afterwards to comply with tax, accounting, and consumer-protection obligations under Indian law (including GST record-keeping requirements). You may request deletion of your account as described below, subject to information we are legally required to retain.</p>

      <h2 id="your-rights">7. Your rights &amp; choices</h2>
      <ul>
        <li>You can review and update your account details (name, addresses, business details) at any time from your profile.</li>
        <li>You can opt out of promotional SMS/email/push notifications while continuing to receive essential order and delivery updates.</li>
        <li>You can delete your account at any time from the Hardware Centers app under Profile &rarr; Delete Account. This removes your profile details, saved addresses, wishlist, cart and reviews; past orders and invoices are kept in anonymised form where Indian tax law requires it.</li>
        <li>You can request a copy of the personal data we hold about you, or request that your account be deleted, by contacting us using the details below.</li>
      </ul>

      <h2 id="security">8. Security</h2>
      <p>We use reasonable technical and organisational measures — including encrypted connections (HTTPS), OTP-based authentication, and access controls on internal systems — to protect your information. No method of transmission or storage is completely secure, so we cannot guarantee absolute security, but we work to protect your data against unauthorised access, alteration, or disclosure.</p>

      <h2 id="children">9. Children's privacy</h2>
      <p>The Platform is intended for use by businesses and individuals aged 18 and above for commercial hardware purchases. We do not knowingly collect personal information from children.</p>

      <h2 id="changes">10. Changes to this policy</h2>
      <p>We may update this Privacy Policy from time to time to reflect changes in our practices or for legal reasons. The "Last updated" date at the top of this page indicates when it was last revised. Continued use of the Platform after a change constitutes acceptance of the updated policy.</p>

      <h2 id="contact">11. Contact us</h2>
      <p>If you have questions about this Privacy Policy or how your data is handled, reach us at:</p>
      <ul>
        <li>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></li>
        @if($phone)<li>Phone: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>@endif
        @if($address)<li>Registered address: {{ $address }}</li>@endif
      </ul>
    </div>
  </div>
</section>
@endsection
