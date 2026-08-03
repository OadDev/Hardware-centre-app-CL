<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('gateway_provider')->nullable()->after('payment_method');
            $table->string('gateway_order_id')->nullable()->after('gateway_provider');
            $table->string('gateway_payment_id')->nullable()->after('gateway_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['gateway_provider', 'gateway_order_id', 'gateway_payment_id']);
        });
    }
};
