@extends('web.layout')

@section('title', 'Contact Us · Hardware Centre')
@section('description', 'Get in touch with Hardware Centre for order, billing, or partnership queries.')

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Contact Us</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg> We'd love to hear from you</span>
    <h1 style="margin-top:14px;">Get in touch with Hardware Centre</h1>
    <p>Questions about an order, a supplier partnership, or feedback on the app — our team typically replies within 24 hours.</p>
  </div>
</section>

<section class="section-tight">
  <div class="container"><div class="info-grid">
    <div class="info-card"><div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg></div><h4>Head Office</h4><p>4th Floor, Vishal Enclave, Rajendra Place, New Delhi 110008, India</p></div>
    <div class="info-card"><div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div><h4>Call Us</h4><p>Mon–Sat, 9:00 AM – 7:00 PM IST</p><a class="val" href="tel:+911141234567">+91 11 4123 4567</a></div>
    <div class="info-card"><div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 9.5 6.5a1 1 0 0 0 1 0L22 7"/></svg></div><h4>Email Us</h4><p>For order, billing and partnership queries</p><a class="val" href="mailto:support@hardwarecentre.in">support@hardwarecentre.in</a></div>
  </div></div>
</section>

<section class="section-tight">
  <div class="container" style="display:grid;grid-template-columns:1.1fr .9fr;gap:44px;align-items:start;">
    <div class="card" style="padding:32px;">
      <h2 class="section-title" style="font-size:22px;">Send us a message</h2>
      <p class="section-sub" style="margin-top:8px;">Fill out the form and our team will get back to you shortly.</p>
      <form class="form-grid" style="margin-top:24px;" data-api-form="{{ route('api.web.contact') }}">
        <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="Rajesh Kumar" required /></div>
        <div class="field"><label>Phone Number</label><input type="tel" name="phone" placeholder="+91 98765 43210" required /></div>
        <div class="field full"><label>Email Address</label><input type="email" name="email" placeholder="you@company.com" required /></div>
        <div class="field full"><label>Subject</label>
          <select name="subject" required>
            <option value="">Select a topic</option>
            <option>Order support</option>
            <option>Supplier partnership</option>
            <option>Billing &amp; invoices</option>
            <option>App feedback</option>
            <option>Other</option>
          </select>
        </div>
        <div class="field full"><label>Message</label><textarea name="message" placeholder="Tell us how we can help…" required></textarea></div>
        <div class="field full"><button type="submit" class="btn btn-primary btn-block">Send Message</button></div>
      </form>
    </div>
    <div>
      <div class="ph-box" style="height:260px;margin-bottom:20px;background:linear-gradient(135deg,#F8FAFC,#EEF1F4);"><span>Map — Hardware Centre HQ, New Delhi</span></div>
      <div class="card" style="padding:22px;">
        <div style="display:flex;align-items:center;gap:12px;"><span style="width:40px;height:40px;border-radius:12px;background:var(--orange-light-bg);display:flex;align-items:center;justify-content:center;"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><div style="font-size:14px;font-weight:700;">Support Hours</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Mon–Sat · 9:00 AM – 7:00 PM IST</div></div></div>
        <div style="height:1px;background:var(--border-soft);margin:16px 0;"></div>
        <div style="font-size:13px;color:var(--muted);line-height:1.7;">Need faster help? Visit our <a href="{{ route('web.support') }}" style="color:var(--orange-dark);font-weight:700;">Help Center</a> or <a href="{{ route('web.track-order') }}" style="color:var(--orange-dark);font-weight:700;">track an existing order</a>.</div>
      </div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container" style="text-align:center;">
    <h3 class="section-title" style="font-size:22px;">Have a quick question?</h3>
    <p class="section-sub" style="margin:10px auto 0;">Check our Help Center for instant answers on orders, returns and payments.</p>
    <a href="{{ route('web.support') }}" class="btn btn-dark" style="margin-top:20px;">Visit Help Center</a>
  </div>
</section>
@endsection
