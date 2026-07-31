<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Before the /setup wizard has run, the database session/cache stores
        // configured in .env don't exist yet — fall back to file-based storage
        // so the wizard's own form (CSRF, validation errors) still works.
        if (! file_exists(storage_path('app/installed.lock'))) {
            if (config('session.driver') === 'database') {
                config(['session.driver' => 'file']);
            }
            if (config('cache.default') === 'database') {
                config(['cache.default' => 'file']);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('web.*', function ($view) {
            $view->with('navCategories', Category::topLevel()->where('status', 'Active')->orderBy('sort_order')->with([
                'children' => fn ($q) => $q->where('status', 'Active')->orderBy('sort_order')->limit(4),
            ])->get());
        });
    }
}
