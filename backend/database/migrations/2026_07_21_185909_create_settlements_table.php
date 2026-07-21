<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_no')->unique();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['Paid', 'Pending', 'Failed'])->default('Pending');
            $table->enum('mode', ['Razorpay Route', 'Cashfree Easy Split'])->default('Razorpay Route');
            $table->string('utr')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
