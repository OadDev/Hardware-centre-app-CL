<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GstController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\ShiprocketController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->name('api.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'stats']);

    Route::apiResource('sellers', SellerController::class)->only(['index', 'store', 'show', 'update']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'update']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('products/{product}/images', [ProductController::class, 'storeImages']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::apiResource('returns', ReturnController::class)->only(['index', 'update']);
    Route::apiResource('coupons', CouponController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('commission', [CommissionController::class, 'index']);
    Route::patch('commission/sellers/{seller}', [CommissionController::class, 'updateSeller']);
    Route::patch('commission/tiers/{tier}', [CommissionController::class, 'updateTier']);

    Route::get('settlements', [SettlementController::class, 'index']);
    Route::post('settlements/{settlement}/retry', [SettlementController::class, 'retry']);

    Route::get('reports/revenue-vs-commission', [ReportController::class, 'revenueVsCommission']);
    Route::get('reports/top-sellers', [ReportController::class, 'topSellers']);

    Route::get('gst', [GstController::class, 'index']);
    Route::post('gst/hsn', [GstController::class, 'store']);
    Route::patch('gst/hsn/{hsnCode}', [GstController::class, 'update']);
    Route::patch('gst/settings', [GstController::class, 'updateSettings']);

    Route::get('shipping', [ShippingController::class, 'index']);
    Route::patch('shipping/config', [ShippingController::class, 'updateConfig']);
    Route::post('shipping/zones', [ShippingController::class, 'storeZone']);
    Route::patch('shipping/zones/{zone}', [ShippingController::class, 'updateZone']);

    Route::get('payments', [PaymentController::class, 'index']);
    Route::patch('payments/{config}', [PaymentController::class, 'update']);
    Route::post('payments/{config}/test', [PaymentController::class, 'test']);

    Route::get('shiprocket', [ShiprocketController::class, 'show']);
    Route::patch('shiprocket', [ShiprocketController::class, 'update']);
    Route::post('shiprocket/test', [ShiprocketController::class, 'test']);
    Route::post('shiprocket/sync', [ShiprocketController::class, 'sync']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/channels/{channel}', [NotificationController::class, 'updateChannel']);
    Route::patch('notifications/templates/{template}', [NotificationController::class, 'updateTemplate']);
    Route::post('notifications/broadcast', [NotificationController::class, 'broadcast']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::patch('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
    Route::post('roles/invite', [RoleController::class, 'invite']);

    Route::get('settings', [SettingController::class, 'show']);
    Route::patch('settings', [SettingController::class, 'update']);
});
