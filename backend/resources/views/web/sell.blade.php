@extends('web.layout')

@section('title', 'Become a Seller · Hardware Centers')
@section('description', 'Join 1,200+ GST-verified sellers reaching buyers across 500+ Indian cities on Hardware Centers.')

@section('content')
<section class="hero">
  <div class="container" style="grid-template-columns:1.1fr .9fr;">
    <div>
      <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><path d="M3 21V8l6-3 6 3v13"/><path d="M15 11h6v10h-6"/><path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0"/></svg> For Suppliers &amp; Manufacturers</span>
      <h1 style="font-size:40px;">Grow your hardware business <span class="accent">online</span></h1>
      <p>Join 1,200+ GST-verified sellers reaching contractors, retailers and buyers across 500+ Indian cities — with zero listing fees to start.</p>
      <div class="hero-cta">
        <a href="#seller-form" class="btn btn-primary">Start Selling Today</a>
        <a href="#how-it-works" class="btn btn-outline-light">How it Works</a>
      </div>
      <div class="hero-stats">
        <div><div class="n">1,200+</div><div class="l">Active sellers</div></div>
        <div><div class="n">₹4.2Cr+</div><div class="l">Paid out monthly</div></div>
        <div><div class="n">48 hrs</div><div class="l">Avg. verification time</div></div>
      </div>
    </div>
    <div class="hero-art">
      <div class="hero-panel">
        <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:var(--shadow-pop);">
          <div style="font-size:12px;color:var(--muted-2);font-weight:700;text-transform:uppercase;">This Month</div>
          <div style="font-family:'Manrope',sans-serif;font-size:26px;font-weight:800;margin-top:6px;">₹3,84,200</div>
          <div style="display:flex;align-items:center;gap:6px;margin-top:6px;color:#16A34A;font-size:12.5px;font-weight:700;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2"><path d="M17 7 7 17M7 7h10v10"/></svg> +24% vs last month</div>
          <div style="height:1px;background:var(--border-soft);margin:16px 0;"></div>
          <div style="display:flex;justify-content:space-between;font-size:12.5px;"><span style="color:var(--muted);">Orders fulfilled</span><b>312</b></div>
          <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-top:8px;"><span style="color:var(--muted);">Seller rating</span><b>4.8 ★</b></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head center"><span class="eyebrow">Why sell with us</span><h2 class="section-title" style="margin-top:14px;">Everything you need to sell online</h2></div>
    <div class="benefit-grid">
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg></div><h4>Pan-India reach</h4><p>Get discovered by verified buyers in 500+ cities without your own logistics network.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 5v6c0 5.5 3.4 9 8 11 4.6-2 8-5.5 8-11V5z"/></svg></div><h4>GST-compliant invoicing</h4><p>Automatic invoice generation and tax handling for every order — no manual paperwork.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></div><h4>Zero listing fees</h4><p>List your first 50 products for free. Pay only a small commission when you sell.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><rect x="1" y="6" width="13" height="11" rx="1.5"/><path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div><h4>Logistics support</h4><p>Optional pickup and delivery partners built for bulky, heavy hardware shipments.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M3 3v18h18"/><path d="M18 9l-5 5-4-4-4 4"/></svg></div><h4>Seller dashboard</h4><p>Track orders, revenue and inventory in real time from web or the seller app.</p></div>
      <div class="benefit-card"><div class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div><h4>Dedicated support</h4><p>A dedicated account manager for sellers doing 100+ orders a month.</p></div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);" id="how-it-works">
  <div class="container">
    <div class="section-head center"><span class="eyebrow">Getting started</span><h2 class="section-title" style="margin-top:14px;">Start selling in 4 simple steps</h2></div>
    <div class="steps-row">
      <div class="step-card"><div class="step-num">01</div><h4>Register your business</h4><p>Sign up with your business name, contact details and category.</p></div>
      <div class="step-card"><div class="step-num">02</div><h4>Verify your GST</h4><p>Upload your GST certificate and PAN for a quick 48-hour verification.</p></div>
      <div class="step-card"><div class="step-num">03</div><h4>List your products</h4><p>Add products with photos, pricing and stock — in bulk via spreadsheet or one by one.</p></div>
      <div class="step-card"><div class="step-num">04</div><h4>Start receiving orders</h4><p>Get notified instantly, fulfill orders, and get paid on a weekly cycle.</p></div>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:820px;">
    <div class="card" style="padding:34px;display:flex;gap:22px;align-items:flex-start;">
      <div style="width:56px;height:56px;border-radius:16px;background:var(--navy);flex-shrink:0;display:flex;align-items:center;justify-content:center;"><span style="font-family:'Manrope',sans-serif;font-weight:800;color:#fff;font-size:20px;">SB</span></div>
      <div>
        <div style="display:flex;gap:3px;">
          @for($i=0;$i<5;$i++)<svg width="14" height="14" viewBox="0 0 24 24" fill="#F59E0B" stroke="none" stroke-width="0"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg>@endfor
        </div>
        <p style="margin-top:10px;font-size:15px;color:#374151;line-height:1.7;">"Since listing on Hardware Centers, our monthly order volume has tripled. The seller dashboard makes it easy to manage inventory across our 3 warehouses, and payouts are always on time."</p>
        <div style="margin-top:14px;font-size:13.5px;font-weight:700;">Shree Balaji Steel Corp <span style="color:var(--muted);font-weight:500;">· Ahmedabad, Gujarat</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);" id="seller-form">
  <div class="container" style="max-width:720px;">
    <div class="card" style="padding:34px;">
      <h2 class="section-title" style="font-size:22px;">Register as a seller</h2>
      <p class="section-sub" style="margin-top:8px;">Takes under 5 minutes. Our team will reach out within 48 hours to verify your details.</p>
      <form class="form-grid" style="margin-top:24px;" data-api-form="{{ route('api.web.sell') }}">
        <div class="field"><label>Business Name</label><input type="text" name="store_name" placeholder="Kumar Hardware & Traders" required /></div>
        <div class="field"><label>Contact Person</label><input type="text" name="owner_name" placeholder="Rajesh Kumar" required /></div>
        <div class="field"><label>Phone Number</label><input type="tel" name="phone" placeholder="+91 98765 43210" required /></div>
        <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="you@business.in" required /></div>
        <div class="field"><label>GST Number</label><input type="text" name="gst_number" placeholder="07AABCK1234M1Z5" required /></div>
        <div class="field"><label>Primary Category</label>
          <select name="category_interest" required>
            <option value="">Select category</option>
            @foreach($navCategories as $cat)<option>{{ $cat->name }}</option>@endforeach
            <option>More</option>
          </select>
        </div>
        <div class="field full"><label>Business Address</label><textarea name="warehouse_address" placeholder="Warehouse / shop address"></textarea></div>
        <div class="field"><label>Create a Password</label><input type="password" name="password" minlength="8" placeholder="At least 8 characters" required /></div>
        <div class="field full" style="font-size:12px;color:var(--muted);margin-top:-6px;">You'll use this to sign in to your Seller Center at <a href="{{ route('seller.login') }}" style="color:var(--orange-dark);font-weight:700;">/seller/login</a> and track your application.</div>
        <div class="field full"><label class="field-check"><input type="checkbox" required /> I agree to the Hardware Centers Seller Terms &amp; Conditions and confirm the information above is accurate.</label></div>
        <div class="field full"><button type="submit" class="btn btn-primary btn-block">Submit Application</button></div>
      </form>
    </div>
  </div>
</section>
@endsection
