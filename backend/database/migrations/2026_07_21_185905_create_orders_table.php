<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('shipping_method', ['standard', 'express'])->default('standard');
            $table->enum('payment_method', ['upi', 'card', 'netbanking', 'cod'])->default('upi');
            $table->enum('payment_status', ['Paid', 'Pending', 'Unpaid', 'Refunded'])->default('Pending');
            $table->enum('status', ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'])->default('Pending');
            $table->string('courier')->nullable();
            $table->string('awb')->nullable();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
