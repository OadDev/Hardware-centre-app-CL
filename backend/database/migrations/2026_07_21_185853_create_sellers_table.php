<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('seller_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('store_name');
            $table->string('owner_name');
            $table->string('email');
            $table->string('phone');
            $table->string('city');
            $table->string('state');
            $table->string('warehouse_address')->nullable();
            $table->string('gst_number')->nullable();
            $table->decimal('commission_pct', 5, 2)->default(10);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->enum('approval_status', ['Approved', 'Pending', 'Rejected'])->default('Pending');
            $table->enum('account_status', ['Active', 'Inactive', 'Suspended'])->default('Inactive');
            $table->enum('kyc_status', ['Verified', 'Under Review', 'Rejected'])->default('Under Review');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
