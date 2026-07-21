<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shiprocket_configs', function (Blueprint $table) {
            $table->id();
            $table->string('api_email')->nullable();
            $table->text('api_password')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('default_courier')->default('Auto Recommend (Best Rate)');
            $table->string('warehouse_name')->nullable();
            $table->string('pickup_address')->nullable();
            $table->boolean('auto_assign_courier')->default(true);
            $table->boolean('auto_generate_awb')->default(true);
            $table->boolean('generate_labels')->default(true);
            $table->boolean('live_tracking')->default(true);
            $table->boolean('connected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shiprocket_configs');
    }
};
