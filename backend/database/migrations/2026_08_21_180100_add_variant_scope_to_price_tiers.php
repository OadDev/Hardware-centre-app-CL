<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_id', 'product_variant_id', 'min_qty'], 'price_tiers_product_variant_qty_unique');
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'min_qty']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('apply_bulk_pricing_to_all_variants')->default(false)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('apply_bulk_pricing_to_all_variants');
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->unique(['product_id', 'min_qty']);
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->dropUnique('price_tiers_product_variant_qty_unique');
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
