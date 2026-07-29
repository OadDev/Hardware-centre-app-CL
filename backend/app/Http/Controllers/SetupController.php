<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

class SetupController extends Controller
{
    protected function lockFile(): string
    {
        return storage_path('app/installed.lock');
    }

    public function show()
    {
        if (file_exists($this->lockFile())) {
            return view('setup.done', ['justInstalled' => session('setup_success', false)]);
        }

        return view('setup.form');
    }

    public function store(Request $request): RedirectResponse
    {
        if (file_exists($this->lockFile())) {
            abort(403, 'This application has already been installed.');
        }

        $configuredToken = env('SETUP_TOKEN');
        if (empty($configuredToken)) {
            return back()->withInput()->withErrors([
                'setup_token' => 'SETUP_TOKEN is not configured on the server. Add SETUP_TOKEN=<a secret value> to the .env file first, then reload this page.',
            ]);
        }

        $data = $request->validate([
            'setup_token' => ['required', 'string'],
            'app_url' => ['required', 'url'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
            'admin_email' => ['required', 'email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        if (! hash_equals((string) $configuredToken, $data['setup_token'])) {
            return back()->withInput()->withErrors(['setup_token' => 'Incorrect setup token.']);
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s', $data['db_host'], $data['db_port']),
                $data['db_username'],
                $data['db_password'] ?? '',
                [PDO::ATTR_TIMEOUT => 5]
            );
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $data['db_database']).'`');
        } catch (PDOException $e) {
            return back()->withInput()->withErrors([
                'db_host' => 'Could not connect to MySQL with these credentials: '.$e->getMessage(),
            ]);
        }

        $this->writeEnv([
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $data['app_url'],
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        Artisan::call('config:clear');

        if (empty(env('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);

        $admin = User::where('email', 'admin@hardwarecentre.in')->first();
        if ($admin) {
            $admin->update([
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
            ]);
        }

        Artisan::call('config:cache');
        Artisan::call('route:cache');

        file_put_contents($this->lockFile(), 'installed_at='.now()->toIso8601String()."\n");

        return redirect()->route('setup.show')->with('setup_success', true);
    }

    /**
     * Update (or append) KEY=value lines in the physical .env file.
     *
     * @param  array<string, string>  $values
     */
    protected function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $escaped = preg_match('/\s|#|"/', $value) ? '"'.str_replace('"', '\"', $value).'"' : $value;
            $line = $key.'='.$escaped;

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $contents)) {
                $contents = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $contents);
            } else {
                $contents = rtrim($contents)."\n".$line."\n";
            }
        }

        file_put_contents($path, $contents);
    }
}
