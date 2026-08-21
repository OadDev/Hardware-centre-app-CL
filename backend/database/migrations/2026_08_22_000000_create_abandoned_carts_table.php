<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cart_value', 12, 2);
            $table->enum('status', ['Abandoned', 'Purchased'])->default('Abandoned');
            $table->timestamp('abandoned_at');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'product_id', 'product_variant_id', 'status'], 'abandoned_carts_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
