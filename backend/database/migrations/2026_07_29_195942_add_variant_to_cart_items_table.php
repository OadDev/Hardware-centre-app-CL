<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['customer_id', 'product_id', 'product_variant_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['customer_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['customer_id', 'product_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
