<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Seller Centre') · Hardware Centre</title>
<link rel="stylesheet" href="{{ asset('vendor/admin.css') }}">
</head>
<body class="bg-ink-50 text-ink-800 antialiased min-h-screen flex">
  <aside class="w-60 shrink-0 bg-white border-r border-ink-200/70 flex flex-col">
    <div class="px-5 py-5 flex items-center gap-2.5 border-b border-ink-200/70">
      <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm">HC</div>
      <div>
        <div class="font-bold text-sm text-ink-900 leading-tight">Seller Centre</div>
        <div class="text-xs text-ink-400">Hardware Centre</div>
      </div>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
      @php($current = request()->routeIs('seller.dashboard') ? 'dashboard' : (request()->routeIs('seller.products') ? 'products' : (request()->routeIs('seller.orders') ? 'orders' : (request()->routeIs('seller.warehouse') ? 'warehouse' : (request()->routeIs('seller.settlements') ? 'settlements' : (request()->routeIs('seller.returns') ? 'returns' : (request()->routeIs('seller.profile') ? 'profile' : '')))))))
      @foreach([['dashboard','Dashboard','seller.dashboard'],['products','My Products','seller.products'],['orders','My Orders','seller.orders'],['warehouse','Warehouse','seller.warehouse'],['settlements','Settlements','seller.settlements'],['returns','Returns','seller.returns'],['profile','Store Profile','seller.profile']] as [$key, $label, $route])
      <a href="{{ route($route) }}" class="block px-3 py-2.5 rounded-xl font-medium {{ $current === $key ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-ink-50' }}">{{ $label }}</a>
      @endforeach
    </nav>
    <div class="p-3 border-t border-ink-200/70">
      <form method="POST" action="{{ route('seller.logout') }}">
        @csrf
        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50">Sign out</button>
      </form>
    </div>
  </aside>

  <div class="flex-1 min-w-0">
    <header class="h-16 bg-white border-b border-ink-200/70 flex items-center justify-between px-6">
      <h1 class="text-lg font-bold text-ink-900">@yield('title', 'Dashboard')</h1>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-ink-800 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($seller->store_name ?? 'S', 0, 1)) }}</div>
        <div class="text-sm">
          <div class="font-semibold text-ink-900 leading-tight">{{ $seller->store_name ?? 'Seller' }}</div>
          <div class="text-xs text-ink-400">{{ $seller->seller_code ?? '' }}</div>
        </div>
      </div>
    </header>

    <main class="p-6">
      @if(session('status'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-medium">{{ session('status') }}</div>
      @endif
      @if($errors->any())
      <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-600 text-sm font-medium">{{ $errors->first() }}</div>
      @endif

      @if($seller && $seller->approval_status !== 'Approved')
      <div class="mb-5 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium">
        Your seller application is <b>{{ $seller->approval_status }}</b>. You can update your store profile now, but products and payouts unlock once our team approves your account.
      </div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
