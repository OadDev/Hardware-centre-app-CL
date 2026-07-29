<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\WebLeadController;
use App\Http\Controllers\Api\V1\WebOrderLookupController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('auth/otp/channel', [AuthController::class, 'otpChannel']);
Route::post('auth/otp/request', [AuthController::class, 'requestOtp']);
Route::post('auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('auth/guest', [AuthController::class, 'guest']);

Route::post('web/contact', [WebLeadController::class, 'contact'])->name('api.web.contact');
Route::post('web/support-ticket', [WebLeadController::class, 'supportTicket'])->name('api.web.support-ticket');
Route::post('web/sell', [WebLeadController::class, 'sell'])->name('api.web.sell');
Route::post('web/newsletter', [WebLeadController::class, 'newsletter'])->name('api.web.newsletter');
Route::post('web/track-order', [WebOrderLookupController::class, 'track'])->name('api.web.track-order');
Route::post('web/my-orders', [WebOrderLookupController::class, 'myOrders'])->name('api.web.my-orders');

Route::middleware('customer.optional')->group(function () {
    Route::get('home', HomeController::class);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category:icon}/products', [CategoryController::class, 'products']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:product_code}', [ProductController::class, 'show']);
    Route::get('suppliers', SupplierController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/items', [CartController::class, 'store']);
    Route::patch('cart/items/{product:product_code}', [CartController::class, 'update']);
    Route::delete('cart/items/{product:product_code}', [CartController::class, 'destroy']);

    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/{product:product_code}', [WishlistController::class, 'store']);
    Route::delete('wishlist/{product:product_code}', [WishlistController::class, 'destroy']);

    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);

    Route::post('coupons/apply', [CouponController::class, 'apply']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order:order_no}', [OrderController::class, 'show']);
    Route::post('orders/{order:order_no}/reorder', [OrderController::class, 'reorder']);
});
