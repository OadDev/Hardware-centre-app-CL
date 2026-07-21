<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['Return', 'Refund']);
            $table->string('reason');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
