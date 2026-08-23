<?php

use App\Http\Controllers\Admin\AbandonedCartController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GstController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OnboardingSlideController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\ShiprocketController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->name('api.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'stats']);

    Route::apiResource('sellers', SellerController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('sellers/{seller}/link-razorpay', [SellerController::class, 'linkRazorpay']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'update']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('products/{product}/images', [ProductController::class, 'storeImages']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant']);
    Route::patch('products/{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
    Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant']);
    Route::post('products/{product}/price-tiers', [ProductController::class, 'storePriceTier']);
    Route::delete('products/{product}/price-tiers/{tier}', [ProductController::class, 'destroyPriceTier']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('categories/{category}/icon', [CategoryController::class, 'uploadIcon']);
    Route::post('categories/{category}/icon-class', [CategoryController::class, 'setIconClass']);
    Route::delete('categories/{category}/icon', [CategoryController::class, 'destroyIcon']);
    Route::apiResource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('brands/{brand}/logo', [BrandController::class, 'uploadLogo']);
    Route::delete('brands/{brand}/logo', [BrandController::class, 'destroyLogo']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::apiResource('returns', ReturnController::class)->only(['index', 'update']);
    Route::apiResource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
    Route::apiResource('coupons', CouponController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('banners/{banner}/image', [BannerController::class, 'uploadImage']);
    Route::delete('banners/{banner}/image', [BannerController::class, 'destroyImage']);
    Route::apiResource('onboarding-slides', OnboardingSlideController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('onboarding-slides/{onboardingSlide}/image', [OnboardingSlideController::class, 'uploadImage']);
    Route::delete('onboarding-slides/{onboardingSlide}/image', [OnboardingSlideController::class, 'destroyImage']);

    Route::get('commission', [CommissionController::class, 'index']);
    Route::patch('commission/sellers/{seller}', [CommissionController::class, 'updateSeller']);
    Route::get('commission/sellers/{seller}/categories', [CommissionController::class, 'categoryOverrides']);
    Route::post('commission/sellers/{seller}/categories', [CommissionController::class, 'setCategoryOverride']);
    Route::delete('commission/sellers/{seller}/categories/{category}', [CommissionController::class, 'removeCategoryOverride']);
    Route::patch('commission/tiers/{tier}', [CommissionController::class, 'updateTier']);

    Route::get('warehouses', [WarehouseController::class, 'index']);
    Route::post('warehouses', [WarehouseController::class, 'store']);
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update']);
    Route::get('warehouses/inventory', [WarehouseController::class, 'inventory']);
    Route::patch('warehouses/inventory/{inventory}', [WarehouseController::class, 'adjustInventory']);
    Route::get('warehouses/shipments', [WarehouseController::class, 'shipments']);
    Route::get('warehouses/shipments/{shipment}', [WarehouseController::class, 'showShipment']);
    Route::post('warehouses/shipments/{shipment}/receive', [WarehouseController::class, 'receiveShipment']);
    Route::get('warehouses/dispatch-queue', [WarehouseController::class, 'dispatchQueue']);
    Route::post('warehouses/dispatch-queue/{item}/pack', [WarehouseController::class, 'packItem']);
    Route::post('warehouses/dispatch-queue/{item}/dispatch', [WarehouseController::class, 'dispatchItem']);

    Route::get('settlements', [SettlementController::class, 'index']);
    Route::post('settlements/{settlement}/mark-paid', [SettlementController::class, 'markPaid']);

    Route::get('reports/revenue-vs-commission', [ReportController::class, 'revenueVsCommission']);
    Route::get('reports/top-sellers', [ReportController::class, 'topSellers']);

    Route::get('gst', [GstController::class, 'index']);
    Route::post('gst/hsn', [GstController::class, 'store']);
    Route::patch('gst/hsn/{hsnCode}', [GstController::class, 'update']);
    Route::patch('gst/settings', [GstController::class, 'updateSettings']);

    Route::get('abandoned-carts', [AbandonedCartController::class, 'index']);

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
    Route::post('notifications/test-push', [NotificationController::class, 'testPush']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::patch('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
    Route::post('roles/invite', [RoleController::class, 'invite']);

    Route::get('settings', [SettingController::class, 'show']);
    Route::patch('settings', [SettingController::class, 'update']);
    Route::post('settings/test-sms', [SettingController::class, 'testSms']);
});
