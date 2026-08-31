<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Packing Slip {{ $order->order_no }}</title>
<style>
  @page { margin: 28px 32px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1F2937; }
  table { border-collapse: collapse; width: 100%; }
  .header-table td { vertical-align: top; }
  .brand { font-size: 20px; font-weight: bold; color: #1F2937; }
  .muted { color: #6B7280; }
  .small { font-size: 10px; }
  .right { text-align: right; }
  .mt-16 { margin-top: 16px; }
  .mt-24 { margin-top: 24px; }
  .doc-title { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1F2937; }
  .box { border: 1px solid #E5E7EB; border-radius: 4px; padding: 10px 12px; }
  .box .label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; color: #9CA3AF; margin-bottom: 4px; }
  .items-table th { background: #F3F4F6; padding: 7px 8px; font-size: 9.5px; text-transform: uppercase; letter-spacing: .3px; color: #4B5563; text-align: left; border-bottom: 1px solid #E5E7EB; }
  .items-table td { padding: 7px 8px; border-bottom: 1px solid #F3F4F6; font-size: 10.5px; }
  .footer { margin-top: 30px; font-size: 9.5px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 10px; }
</style>
</head>
<body>

<table class="header-table">
  <tr>
    <td style="width: 60%;">
      <div class="brand">{{ $seller->store_name }}</div>
      @if($seller->warehouse_address)<div class="muted small mt-16" style="white-space: pre-line;">{{ $seller->warehouse_address }}</div>@endif
      @if($seller->city || $seller->state)<div class="muted small">{{ $seller->city }}@if($seller->city && $seller->state), @endif{{ $seller->state }}</div>@endif
      @if($seller->phone)<div class="muted small">Phone: {{ $seller->phone }}</div>@endif
    </td>
    <td style="width: 40%;" class="right">
      <div class="doc-title">Packing Slip</div>
      <div class="small mt-16"><span class="muted">Order No:</span> <strong>{{ $order->order_no }}</strong></div>
      <div class="small"><span class="muted">Order Date:</span> {{ $order->placed_at->format('d M Y') }}</div>
      <div class="small"><span class="muted">Payment:</span> {{ strtoupper($order->payment_method) }}</div>
    </td>
  </tr>
</table>

<table class="mt-24">
  <tr>
    <td style="width: 100%;">
      <div class="box">
        <div class="label">Ship To</div>
        <div><strong>{{ $order->address->contact_name ?? $order->customer->name ?? 'Guest' }}</strong></div>
        @if($order->address)
        <div class="small mt-16">{{ $order->address->line }}</div>
        <div class="small">{{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->pincode }}</div>
        @endif
        @if($order->customer?->phone)<div class="small">Phone: {{ $order->customer->phone }}</div>@endif
      </div>
    </td>
  </tr>
</table>

<table class="items-table mt-24">
  <thead>
    <tr>
      <th style="width: 8%;">#</th>
      <th style="width: 62%;">Item</th>
      <th style="width: 30%;" class="right">Qty</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $i => $item)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $item->product->name ?? 'Product removed' }}@if($item->variant_label) <span class="muted small">({{ $item->variant_label }})</span>@endif</td>
      <td class="right">{{ $item->quantity }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">
  <div>Packing slip only — not a tax invoice. Generated on {{ now()->format('d M Y, h:i A') }}.</div>
</div>

</body>
</html>
