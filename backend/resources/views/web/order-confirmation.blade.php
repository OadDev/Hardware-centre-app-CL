@extends('web.layout')

@section('title', 'Order Confirmed · Hardware Centers')
@section('description', 'Your Hardware Centers order has been placed.')

@section('content')
<section class="section-tight" style="padding:70px 0;">
  <div class="container" style="max-width:520px;text-align:center;" id="order-confirmation-page" data-order-no="{{ $orderNo }}">
    <div class="order-success-circle">
      <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <h1 style="font-size:24px;margin-top:24px;">Order Placed!</h1>
    <p style="color:var(--muted);margin-top:10px;font-size:14px;">Your order has been confirmed and will be processed by the supplier shortly.</p>

    <div class="order-success-box" style="margin-top:24px;">
      <span style="font-size:11px;font-weight:700;color:var(--muted-2);letter-spacing:.6px;">ORDER NUMBER</span>
      <span style="font-size:18px;font-weight:800;font-family:var(--font-head);">{{ $orderNo }}</span>
    </div>

    <div data-order-details style="margin-top:20px;display:none;text-align:left;" class="card"></div>

    <div style="display:flex;gap:12px;margin-top:32px;flex-wrap:wrap;justify-content:center;">
      <a href="{{ route('web.orders') }}" class="btn btn-primary">Track Order</a>
      <a href="{{ route('web.home') }}" class="btn btn-outline">Continue Shopping</a>
    </div>
  </div>
</section>
@endsection
