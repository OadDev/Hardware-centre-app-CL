<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('hsn_code_id')->nullable()->constrained('hsn_codes')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('mrp', 10, 2);
            $table->string('unit')->default('piece');
            $table->unsignedInteger('stock')->default(0);
            $table->enum('approval_status', ['Approved', 'Pending', 'Rejected'])->default('Pending');
            $table->boolean('featured')->default(false);
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->unsignedTinyInteger('eta_days')->default(3);
            $table->string('tag')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
