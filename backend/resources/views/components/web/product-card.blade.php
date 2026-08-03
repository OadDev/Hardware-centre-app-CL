@props(['product', 'width' => null])
@php
$p = $product;
$off = $p->mrp > 0 ? round((1 - (float) $p->price / (float) $p->mrp) * 100) : 0;
$tagBg = $p->tag === 'Deal' ? '#FFF7ED' : ($p->tag === 'Bestseller' ? '#FFF7ED' : '#F0FDF4');
$tagColor = in_array($p->tag, ['Deal', 'Bestseller']) ? '#EA580C' : '#16A34A';
@endphp
<a class="prod-card" href="{{ route('web.product', $p->product_code) }}" style="{{ $width ? 'width:'.$width.';flex-shrink:0;' : 'width:100%;' }}" data-brand="{{ $p->brand->name }}" data-search-target="{{ $p->name }} {{ $p->brand->name }}">
  <div class="prod-thumb" style="background:linear-gradient(135deg,#F8FAFC,#EEF1F4);">
    <span>{{ $p->brand->name }}<br>{{ $p->name }}</span>
    @if($p->tag)
    <div class="prod-tag" style="background:{{ $tagBg }};color:{{ $tagColor }};">{{ $p->tag }}</div>
    @endif
    <button class="prod-wish" data-wish-id="{{ $p->product_code }}" aria-label="Save"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.1C.4 8.8 1.6 5.5 4.7 5c2-.3 3.6.8 4.3 2 .7-1.2 2.3-2.3 4.3-2 3.1.5 4.3 3.8 2.7 6.9C19.5 16.4 12 21 12 21Z"/></svg></button>
  </div>
  <div class="prod-body">
    <div class="prod-brand">{{ $p->brand->name }}</div>
    <div class="prod-name">{{ $p->name }}</div>
    <div class="prod-rating"><svg width="12" height="12" viewBox="0 0 24 24" fill="#F59E0B" stroke="none" stroke-width="0" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg> {{ number_format($p->rating, 1) }} <span class="rc">({{ number_format($p->reviews_count) }})</span></div>
    <div class="prod-price-row">
      <div><span class="prod-price">₹{{ number_format($p->price) }}</span>@if($off > 0)<span class="prod-mrp">₹{{ number_format($p->mrp) }}</span>@endif</div>
      <button class="prod-add" data-add-to-cart="{{ $p->product_code }}" aria-label="Add to cart" @if($p->stock <= 0) disabled @endif><svg width="15" height="15" viewBox="0 0 24 24" fill="#fff" stroke="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></button>
    </div>
  </div>
</a>
