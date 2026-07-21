<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->string('display_name');
            $table->string('key_id')->nullable();
            $table->text('secret')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->json('extra')->nullable();
            $table->boolean('sandbox_mode')->default(true);
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_configs');
    }
};
