@extends('web.layout')

@section('title', 'Help & Support · Hardware Centre')
@section('description', 'Get help with orders, payments, returns and your Hardware Centre account.')

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Help &amp; Support</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg> We're here to help</span>
    <h1 style="margin-top:14px;">How can we help you today?</h1>
    <div style="max-width:560px;margin-top:22px;display:flex;align-items:center;gap:10px;height:52px;border-radius:14px;padding:0 16px;background:#fff;box-shadow:var(--shadow-pop);">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
      <input type="text" placeholder="Search for help — 'refund', 'track order', 'GST invoice'…" style="flex:1;border:none;outline:none;font-size:14px;font-family:inherit;" />
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container"><div class="support-grid">
    <div class="support-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div><h4>Live Chat</h4><p>Chat with our support team in real time, available 9 AM – 9 PM.</p><a href="#" class="btn btn-outline btn-sm" style="margin-top:16px;">Start Chat</a></div>
    <div class="support-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div><h4>Call Us</h4><p>Speak directly with a support agent for urgent order issues.</p><a href="tel:+911141234567" class="btn btn-outline btn-sm" style="margin-top:16px;">+91 11 4123 4567</a></div>
    <div class="support-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M3 9a2 2 0 1 0 0 6v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-2a2 2 0 1 1 0-6V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1z"/><path d="M12 8v1M12 15v1M12 11.5v1"/></svg></div><h4>Raise a Ticket</h4><p>Submit a support ticket and track its resolution status.</p><a href="#ticket-form" class="btn btn-outline btn-sm" style="margin-top:16px;">Open Ticket Form</a></div>
  </div></div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container">
    <div class="section-head center"><span class="eyebrow">Browse Topics</span><h2 class="section-title" style="margin-top:14px;">Popular help topics</h2></div>
    <div class="cat-grid" style="grid-template-columns:repeat(4,1fr);">
      <a class="cat-card" href="{{ route('web.track-order') }}"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.7"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div><div class="name">Orders &amp; Tracking</div><div class="count">12 articles</div></a>
      <a class="cat-card" href="#"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.7"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></div><div class="name">Payments &amp; Refunds</div><div class="count">9 articles</div></a>
      <a class="cat-card" href="{{ route('web.sell') }}"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.7"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg></div><div class="name">Selling on Hardware Centre</div><div class="count">15 articles</div></a>
      <a class="cat-card" href="{{ route('web.download-app') }}"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="1.7"><path d="M12 3v12"/><path d="m6 11 6 6 6-6"/><path d="M4 21h16"/></svg></div><div class="name">App &amp; Account</div><div class="count">7 articles</div></a>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:760px;">
    <div class="section-head"><span class="eyebrow">FAQ</span><h2 class="section-title" style="margin-top:14px;">Frequently asked questions</h2></div>
    <div class="faq-list">
      <div class="faq-item"><div class="faq-q">How do I place a bulk order?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">Add items to your cart, select 'Bulk Order' at checkout, and our team will confirm trade pricing and delivery timelines with you within 2 business hours.</div></div></div>
      <div class="faq-item"><div class="faq-q">What payment methods are accepted?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">We accept UPI, all major debit/credit cards, net banking, and 30-day credit terms for verified business accounts.</div></div></div>
      <div class="faq-item"><div class="faq-q">How long does delivery take?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">Standard delivery takes 2–5 business days depending on your city. Express delivery (next-day) is available in 40+ metro areas.</div></div></div>
      <div class="faq-item"><div class="faq-q">Can I return a product?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">Yes — most items can be returned within 7 days of delivery if unused and in original packaging. Custom-cut or made-to-order items are non-returnable.</div></div></div>
      <div class="faq-item"><div class="faq-q">How do I become a verified supplier?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">Visit the Become a Seller page, submit your GST details and business documents, and our verification team will review your application within 48 hours.</div></div></div>
      <div class="faq-item"><div class="faq-q">Is GST invoicing available?<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><div class="faq-a-in">Yes, every order includes a GST-compliant invoice automatically generated after dispatch, available in My Orders.</div></div></div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);" id="ticket-form">
  <div class="container" style="max-width:640px;">
    <div class="card" style="padding:32px;">
      <h2 class="section-title" style="font-size:22px;">Raise a support ticket</h2>
      <p class="section-sub" style="margin-top:8px;">Can't find an answer? Send us the details and we'll follow up by email.</p>
      <form class="form-grid" style="margin-top:22px;" data-api-form="{{ route('api.web.support-ticket') }}">
        <div class="field"><label>Order Number (optional)</label><input type="text" name="order_no" placeholder="HC-2026-04821" /></div>
        <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="you@company.com" required /></div>
        <div class="field full"><label>Issue Type</label>
          <select name="subject" required>
            <option value="">Select an issue</option>
            <option>Order delayed / not delivered</option>
            <option>Wrong or damaged item</option>
            <option>Refund not received</option>
            <option>GST invoice request</option>
            <option>Account &amp; login issue</option>
          </select>
        </div>
        <div class="field full"><label>Describe the issue</label><textarea name="message" placeholder="Tell us what happened…" required></textarea></div>
        <div class="field full"><button type="submit" class="btn btn-primary btn-block">Submit Ticket</button></div>
      </form>
    </div>
  </div>
</section>
@endsection
