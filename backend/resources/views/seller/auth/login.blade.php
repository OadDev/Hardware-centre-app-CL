<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Sign In — Hardware Centers</title>
<link rel="stylesheet" href="{{ asset('vendor/admin.css') }}">
</head>
<body class="min-h-screen bg-ink-50 font-sans flex items-center justify-center p-6">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center mx-auto font-bold text-lg">HC</div>
      <h1 class="mt-4 text-xl font-bold text-ink-900">Hardware Centers Seller Center</h1>
      <p class="text-sm text-ink-500 mt-1">Sign in to manage your store</p>
    </div>

    <div class="bg-white rounded-2xl border border-ink-200/70 shadow-sm p-6">
      @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-600 text-sm">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('seller.login.attempt') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="you@business.in">
        </div>
        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Password</label>
          <input type="password" name="password" required
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="••••••••">
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-600">
          <input type="checkbox" name="remember" class="rounded"> Remember me
        </label>
        <button type="submit" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-colors">
          Sign in
        </button>
      </form>
    </div>

    <p class="text-center text-xs text-ink-400 mt-6">Not a seller yet? <a href="{{ route('web.sell') }}" class="text-brand-600 font-semibold">Apply to sell on Hardware Centers</a></p>
  </div>
</body>
</html>
