<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_configs', function (Blueprint $table) {
            $table->id();
            $table->boolean('cod_enabled')->default(true);
            $table->decimal('cod_fee', 8, 2)->default(25);
            $table->decimal('packaging_fee', 8, 2)->default(15);
            $table->boolean('packaging_apply_all')->default(false);
            $table->boolean('free_shipping_enabled')->default(true);
            $table->decimal('free_shipping_min', 10, 2)->default(999);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_configs');
    }
};
