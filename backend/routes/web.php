<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Seller\AuthController as SellerAuthController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\ProfileController as SellerProfileController;
use App\Http\Controllers\Seller\ReturnController as SellerReturnController;
use App\Http\Controllers\Seller\SettlementController as SellerSettlementController;
use App\Http\Controllers\SetupController;
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

Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

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

    Route::middleware(['auth', 'role:Admin|Manager|Support|Accounts'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::group([], base_path('routes/admin_api.php'));
    });
});

Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('login', [SellerAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [SellerAuthController::class, 'login'])->name('login.attempt');
    Route::post('logout', [SellerAuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'role:Seller'])->group(function () {
        Route::get('/', [SellerDashboardController::class, 'index'])->name('dashboard');

        Route::get('products', [SellerProductController::class, 'index'])->name('products');
        Route::post('products', [SellerProductController::class, 'store'])->name('products.store');
        Route::patch('products/{product:product_code}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product:product_code}', [SellerProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('products/{product:product_code}/images/{image}', [SellerProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::get('products/{product:product_code}/variants', [SellerProductController::class, 'variants'])->name('products.variants');
        Route::post('products/{product:product_code}/variants', [SellerProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::patch('products/{product:product_code}/variants/{variant}', [SellerProductController::class, 'updateVariant'])->name('products.variants.update');
        Route::delete('products/{product:product_code}/variants/{variant}', [SellerProductController::class, 'destroyVariant'])->name('products.variants.destroy');

        Route::get('orders', [SellerOrderController::class, 'index'])->name('orders');
        Route::get('settlements', [SellerSettlementController::class, 'index'])->name('settlements');
        Route::get('returns', [SellerReturnController::class, 'index'])->name('returns');

        Route::get('profile', [SellerProfileController::class, 'edit'])->name('profile');
        Route::patch('profile', [SellerProfileController::class, 'update'])->name('profile.update');
    });
});
