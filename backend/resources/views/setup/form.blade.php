<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup — Hardware Centre</title>
<link rel="stylesheet" href="{{ asset('vendor/admin.css') }}">
</head>
<body class="min-h-screen bg-ink-50 font-sans flex items-center justify-center p-6">
  <div class="w-full max-w-lg">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center mx-auto font-bold text-lg">HC</div>
      <h1 class="mt-4 text-xl font-bold text-ink-900">Hardware Centre — Installation</h1>
      <p class="text-sm text-ink-500 mt-1">One-time setup: connect the database and create your admin login.</p>
    </div>

    <div class="bg-white rounded-2xl border border-ink-200/70 shadow-sm p-6">
      @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-600 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('setup.store') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Setup token</label>
          <input type="password" name="setup_token" required
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="Value of SETUP_TOKEN in your .env file">
          <p class="text-xs text-ink-400 mt-1">Matches the <code>SETUP_TOKEN</code> value set in the server's .env file.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Site URL</label>
          <input type="url" name="app_url" value="{{ old('app_url', 'https://hardwarecenters.com') }}" required
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">Database host</label>
            <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}" required
              class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600">
          </div>
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">Database port</label>
            <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" required
              class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Database name</label>
          <input type="text" name="db_database" value="{{ old('db_database') }}" required
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="e.g. u761085554_hardware">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">Database username</label>
            <input type="text" name="db_username" value="{{ old('db_username') }}" required
              class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600">
          </div>
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">Database password</label>
            <input type="password" name="db_password"
              class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600">
          </div>
        </div>

        <hr class="border-ink-100">

        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Your admin email</label>
          <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="you@example.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-ink-700 mb-1.5">Your admin password</label>
          <input type="password" name="admin_password" required minlength="8"
            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 focus:outline-none focus:ring-2 focus:ring-brand-600"
            placeholder="At least 8 characters">
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-colors">
          Install
        </button>
      </form>
    </div>

    <p class="text-center text-xs text-ink-400 mt-6">This page runs migrations and seed data, then locks itself permanently.</p>
  </div>
</body>
</html>
