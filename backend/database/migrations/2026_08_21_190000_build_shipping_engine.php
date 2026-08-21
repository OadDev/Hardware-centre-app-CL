<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shipping_zones becomes a proper rule table: State + Method +
        // Weight/Quantity/Order-Value thresholds -> Charge, instead of a
        // flat zone label that nothing ever actually matched against.
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->string('state')->nullable()->after('zone');
            $table->string('method')->nullable()->after('state');
            $table->decimal('min_order_value', 10, 2)->default(0)->after('free_above');
            $table->decimal('max_weight_kg', 8, 3)->nullable()->after('min_order_value');
            $table->unsignedInteger('min_qty')->nullable()->after('max_weight_kg');
            $table->unsignedInteger('max_qty')->nullable()->after('min_qty');
            $table->unsignedTinyInteger('eta_days')->nullable()->after('max_qty');
            $table->boolean('is_active')->default(true)->after('eta_days');
        });

        Schema::table('shipping_configs', function (Blueprint $table) {
            $table->decimal('standard_charge', 8, 2)->default(99)->after('free_shipping_min');
            $table->unsignedTinyInteger('standard_eta_days')->default(4)->after('standard_charge');
            $table->boolean('express_enabled')->default(true)->after('standard_eta_days');
            $table->decimal('express_charge', 8, 2)->default(149)->after('express_enabled');
            $table->unsignedTinyInteger('express_eta_days')->default(2)->after('express_charge');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight_kg', 8, 3)->default(0.5)->after('unit');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('pincode');
            $table->string('email')->nullable()->after('gst_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('address_id');
            $table->string('customer_email')->nullable()->after('gst_number');
            $table->unsignedTinyInteger('eta_days')->nullable()->after('shipping_method');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->dropColumn(['state', 'method', 'min_order_value', 'max_weight_kg', 'min_qty', 'max_qty', 'eta_days', 'is_active']);
        });

        Schema::table('shipping_configs', function (Blueprint $table) {
            $table->dropColumn(['standard_charge', 'standard_eta_days', 'express_enabled', 'express_charge', 'express_eta_days']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['gst_number', 'email']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['gst_number', 'customer_email', 'eta_days']);
        });
    }
};
