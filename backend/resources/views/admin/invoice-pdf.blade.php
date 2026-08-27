<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $invoice->invoice_number }}</title>
<style>
  @page { margin: 28px 32px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1F2937; }
  table { border-collapse: collapse; width: 100%; }
  .header-table td { vertical-align: top; }
  .brand { font-size: 20px; font-weight: bold; color: #1F2937; }
  .brand .accent { color: #F97316; }
  .muted { color: #6B7280; }
  .small { font-size: 10px; }
  .right { text-align: right; }
  .center { text-align: center; }
  .mt-16 { margin-top: 16px; }
  .mt-24 { margin-top: 24px; }
  .invoice-title { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1F2937; }
  .box { border: 1px solid #E5E7EB; border-radius: 4px; padding: 10px 12px; }
  .box .label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; color: #9CA3AF; margin-bottom: 4px; }
  .items-table th { background: #F3F4F6; padding: 7px 8px; font-size: 9.5px; text-transform: uppercase; letter-spacing: .3px; color: #4B5563; text-align: left; border-bottom: 1px solid #E5E7EB; }
  .items-table td { padding: 7px 8px; border-bottom: 1px solid #F3F4F6; font-size: 10.5px; }
  .totals-table td { padding: 4px 8px; font-size: 11px; }
  .totals-table .label { color: #6B7280; }
  .totals-table .grand td { border-top: 1.5px solid #1F2937; font-weight: bold; font-size: 13px; padding-top: 8px; }
  .footer { margin-top: 30px; font-size: 9.5px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 10px; }
</style>
</head>
<body>

<table class="header-table">
  <tr>
    <td style="width: 60%;">
      <div class="brand">{{ $invoice->business_name ?: 'Hardware Centers' }}</div>
      @if($invoice->business_address)<div class="muted small mt-16" style="white-space: pre-line;">{{ $invoice->business_address }}</div>@endif
      @if($invoice->business_gst_number)<div class="muted small">GSTIN: {{ $invoice->business_gst_number }}</div>@endif
    </td>
    <td style="width: 40%;" class="right">
      <div class="invoice-title">Tax Invoice</div>
      <div class="small mt-16"><span class="muted">Invoice No:</span> <strong>{{ $invoice->invoice_number }}</strong></div>
      <div class="small"><span class="muted">Invoice Date:</span> {{ $invoice->issued_at->format('d M Y') }}</div>
      <div class="small"><span class="muted">Order No:</span> {{ $order->order_no }}</div>
      <div class="small"><span class="muted">Order Date:</span> {{ $order->placed_at->format('d M Y') }}</div>
    </td>
  </tr>
</table>

<table class="mt-24">
  <tr>
    <td style="width: 50%; padding-right: 10px;">
      <div class="box">
        <div class="label">Bill To</div>
        <div><strong>{{ $invoice->billing_name }}</strong></div>
        @if($invoice->billing_address)<div class="small mt-16">{{ $invoice->billing_address }}</div>@endif
        @if($invoice->billing_phone)<div class="small">Phone: {{ $invoice->billing_phone }}</div>@endif
        @if($invoice->billing_email)<div class="small">Email: {{ $invoice->billing_email }}</div>@endif
        @if($invoice->billing_gst_number)<div class="small">GSTIN: {{ $invoice->billing_gst_number }}</div>@endif
      </div>
    </td>
    <td style="width: 50%; padding-left: 10px;">
      <div class="box">
        <div class="label">Payment</div>
        <div class="small mt-16"><span class="muted">Method:</span> {{ strtoupper($order->payment_method) }}</div>
        <div class="small"><span class="muted">Status:</span> {{ $order->payment_status }}</div>
        @if($order->courier)<div class="small"><span class="muted">Courier:</span> {{ $order->courier }} @if($order->awb) ({{ $order->awb }}) @endif</div>@endif
      </div>
    </td>
  </tr>
</table>

<table class="items-table mt-24">
  <thead>
    <tr>
      <th style="width: 4%;">#</th>
      <th style="width: 30%;">Item</th>
      <th style="width: 10%;">HSN</th>
      <th style="width: 7%;" class="right">Qty</th>
      <th style="width: 12%;" class="right">Unit Price</th>
      <th style="width: 12%;" class="right">Taxable Value</th>
      <th style="width: 8%;" class="right">GST</th>
      <th style="width: 17%;" class="right">Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach($lineItems as $i => $item)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $item['product'] }}@if($item['variant']) <span class="muted small">({{ $item['variant'] }})</span>@endif</td>
      <td>{{ $item['hsnCode'] ?: '—' }}</td>
      <td class="right">{{ $item['quantity'] }}</td>
      <td class="right">₹{{ number_format($item['unitPrice'], 2) }}</td>
      <td class="right">₹{{ number_format($item['taxableValue'], 2) }}</td>
      <td class="right">{{ rtrim(rtrim(number_format($item['gstRate'], 2), '0'), '.') }}%</td>
      <td class="right">₹{{ number_format($item['lineTotal'], 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="mt-16">
  <tr>
    <td style="width: 55%;"></td>
    <td style="width: 45%;">
      <table class="totals-table">
        <tr><td class="label">Subtotal</td><td class="right">₹{{ number_format($invoice->subtotal, 2) }}</td></tr>
        @if($invoice->discount > 0)
        <tr><td class="label">Discount</td><td class="right">−₹{{ number_format($invoice->discount, 2) }}</td></tr>
        @endif
        <tr><td class="label">GST</td><td class="right">₹{{ number_format($invoice->gst_amount, 2) }}</td></tr>
        <tr><td class="label">Delivery / Shipping</td><td class="right">₹{{ number_format($invoice->delivery_charge, 2) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">₹{{ number_format($invoice->total, 2) }}</td></tr>
      </table>
    </td>
  </tr>
</table>

<div class="footer">
  <div>This is a system-generated invoice and does not require a signature.</div>
  <div>Generated on {{ now()->format('d M Y, h:i A') }}{{ $invoice->createdBy?->name ? ' by '.$invoice->createdBy->name : '' }}.</div>
</div>

</body>
</html>
