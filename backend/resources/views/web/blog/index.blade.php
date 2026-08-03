@extends('web.layout')

@section('title', 'Blog · Hardware Centers')
@section('description', 'Buying guides, supplier spotlights and industry trends from Hardware Centers.')

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="crumb"><a href="{{ route('web.home') }}">Home</a> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg> <span style="color:#fff;">Blog</span></div>
    <span class="hero-eyebrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FDBA74" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg> Hardware Centers Journal</span>
    <h1 style="margin-top:14px;">Guides, trends &amp; stories from the trade</h1>
    <p>Buying guides, supplier spotlights and industry trends to help you build, source and sell smarter.</p>
  </div>
</section>

@if($featured)
<section class="section-tight">
  <div class="container">
    <a href="{{ route('web.blog.show', $featured) }}" class="card two-col-split" style="overflow:hidden;">
      <div style="background:linear-gradient(135deg,#1F2937,#111827);min-height:280px;display:flex;align-items:center;justify-content:center;padding:20px;text-align:center;"><span style="color:rgba(255,255,255,.85);font-size:12.5px;font-weight:700;">{{ $featured->category }}</span></div>
      <div style="padding:34px;">
        <span class="badge badge-orange">Featured</span>
        <h2 style="font-size:24px;margin-top:14px;line-height:1.3;">{{ $featured->title }}</h2>
        <p style="font-size:14px;color:var(--muted);margin-top:12px;line-height:1.7;">{{ $featured->excerpt }}</p>
        <div class="blog-meta" style="margin-top:18px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $featured->published_at->format('d M Y') }} · {{ $featured->read_minutes }} min read</div>
      </div>
    </a>
  </div>
</section>
@endif

<section class="section-tight">
  <div class="container">
    <div class="tab-row" style="margin-bottom:26px;">
      <a href="{{ route('web.blog') }}" class="tab-btn {{ !$activeCategory ? 'active' : '' }}">All Posts</a>
      @foreach($categories as $cat)
      <a href="{{ route('web.blog', ['category' => $cat]) }}" class="tab-btn {{ $activeCategory === $cat ? 'active' : '' }}">{{ $cat }}</a>
      @endforeach
    </div>
    <div class="blog-grid">
      @forelse($posts as $post)
      <a class="blog-card" href="{{ route('web.blog.show', $post) }}">
        <div class="blog-thumb" style="background:linear-gradient(135deg,#F97316,#111827);"><span>{{ $post->category }}</span></div>
        <div class="blog-body">
          <div class="blog-cat">{{ $post->category }}</div>
          <div class="blog-title">{{ $post->title }}</div>
          <div class="blog-excerpt">{{ $post->excerpt }}</div>
          <div class="blog-meta"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $post->published_at->format('d M Y') }} · {{ $post->read_minutes }} min read</div>
        </div>
      </a>
      @empty
      <p style="color:var(--muted);">No posts in this category yet.</p>
      @endforelse
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);">
  <div class="container" style="max-width:620px;text-align:center;">
    <h3 class="section-title" style="font-size:22px;">Get the best of the blog in your inbox</h3>
    <p class="section-sub" style="margin:10px auto 0;">One email a month — no spam, unsubscribe any time.</p>
    <form style="display:flex;gap:10px;margin-top:22px;max-width:420px;margin-left:auto;margin-right:auto;" data-api-form="{{ route('api.web.newsletter') }}">
      <input type="email" name="email" required placeholder="you@company.com" style="flex:1;min-width:0;height:50px;border-radius:12px;border:1px solid var(--border);padding:0 16px;font-size:14px;font-family:inherit;" />
      <button class="btn btn-primary" type="submit">Subscribe</button>
    </form>
  </div>
</section>
@endsection
