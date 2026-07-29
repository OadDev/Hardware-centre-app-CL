<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CategoriesController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\DownloadAppController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\OrdersController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\SellController;
use App\Http\Controllers\Web\SupportController;
use App\Http\Controllers\Web\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::name('web.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/about', [AboutController::class, 'index'])->name('about');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::get('/sell', [SellController::class, 'index'])->name('sell');
    Route::get('/support', [SupportController::class, 'index'])->name('support');
    Route::get('/track-order', [TrackOrderController::class, 'index'])->name('track-order');
    Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
    Route::get('/download-app', [DownloadAppController::class, 'index'])->name('download-app');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog');
    Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::group([], base_path('routes/admin_api.php'));
    });
});
