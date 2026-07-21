<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('business_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->enum('status', ['Active', 'Blocked'])->default('Active');
            $table->boolean('is_guest')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
