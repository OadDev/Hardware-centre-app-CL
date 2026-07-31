@extends('web.layout')

@section('title', $post->title.' · Hardware Centers')
@section('description', $post->excerpt)

@section('content')
<section class="page-hero">
  <div class="container" style="max-width:780px;">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <a href="{{ route('web.blog') }}">Blog</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Article</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="#FDBA74" stroke="#FDBA74" stroke-width="0"><path d="M12 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg> {{ $post->category }}</span>
    <h1 style="margin-top:14px;font-size:32px;">{{ $post->title }}</h1>
    <div class="blog-meta" style="color:#cbd5e1;margin-top:16px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $post->published_at->format('d M Y') }} · {{ $post->read_minutes }} min read · By {{ $post->author }}</div>
  </div>
</section>

<section class="section-tight">
  <div class="container" style="max-width:780px;">
    <div class="ph-box" style="height:320px;margin-bottom:30px;background:linear-gradient(135deg,#1F2937,#111827);"><span style="color:rgba(255,255,255,.85);">{{ $post->category }}</span></div>
    <div style="font-size:16px;line-height:1.9;color:#374151;">
      {!! $post->body !!}
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container">
    <h3 class="section-title" style="font-size:20px;margin-bottom:20px;">More from the blog</h3>
    <div class="blog-grid">
      @foreach($related as $r)
      <a class="blog-card" href="{{ route('web.blog.show', $r) }}">
        <div class="blog-thumb" style="background:linear-gradient(135deg,#F97316,#111827);"><span>{{ $r->category }}</span></div>
        <div class="blog-body">
          <div class="blog-cat">{{ $r->category }}</div>
          <div class="blog-title">{{ $r->title }}</div>
          <div class="blog-excerpt">{{ $r->excerpt }}</div>
          <div class="blog-meta"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $r->published_at->format('d M Y') }} · {{ $r->read_minutes }} min read</div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
