<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('fulfillment_type', ['warehouse', 'seller'])->default('seller')->after('seller_id');
            $table->foreignId('warehouse_id')->nullable()->after('fulfillment_type')->constrained()->nullOnDelete();
            $table->enum('dispatch_status', ['Pending', 'Packed', 'Dispatched', 'Cancelled'])->nullable()->after('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropColumn(['fulfillment_type', 'dispatch_status']);
        });
    }
};
