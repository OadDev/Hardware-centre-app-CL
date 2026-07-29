<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup — Hardware Centre</title>
<link rel="stylesheet" href="{{ asset('vendor/admin.css') }}">
</head>
<body class="min-h-screen bg-ink-50 font-sans flex items-center justify-center p-6">
  <div class="w-full max-w-sm text-center">
    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mx-auto font-bold text-lg">✓</div>

    @if ($justInstalled ?? false)
      <h1 class="mt-4 text-xl font-bold text-ink-900">Installation complete</h1>
      <p class="text-sm text-ink-500 mt-2">The database has been migrated and seeded, and your admin account is ready.</p>
    @else
      <h1 class="mt-4 text-xl font-bold text-ink-900">Already installed</h1>
      <p class="text-sm text-ink-500 mt-2">This application has already been set up. This page cannot run again.</p>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-3">
      <a href="{{ route('admin.login') }}" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-colors">
        Go to admin login
      </a>
      <a href="{{ route('web.home') }}" class="w-full py-2.5 rounded-xl border border-ink-200 text-ink-700 text-sm font-semibold transition-colors">
        Go to the website
      </a>
    </div>
  </div>
</body>
</html>
