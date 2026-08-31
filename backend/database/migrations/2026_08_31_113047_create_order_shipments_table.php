<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per seller per order — a multi-vendor order ships as separate
     * parcels from separate pickup locations, so each seller gets their own
     * Shiprocket order/shipment/AWB/label instead of sharing the order's
     * single legacy courier/awb columns.
     */
    public function up(): void
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('shiprocket_order_id')->nullable();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->string('courier')->nullable();
            $table->string('awb')->nullable();
            $table->text('label_url')->nullable();
            $table->string('status')->default('Pending');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
