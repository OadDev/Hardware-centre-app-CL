<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();
            $table->string('razorpay_transfer_id')->nullable()->after('utr');
        });

        // A seller isn't Route-linked from day one — orders placed before their
        // linked account is activated fall back to a manual bank transfer that
        // the admin records by hand, so the settlement needs a mode for that.
        DB::statement("ALTER TABLE settlements MODIFY mode ENUM('Razorpay Route', 'Cashfree Easy Split', 'Manual Bank Transfer') DEFAULT 'Razorpay Route'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE settlements MODIFY mode ENUM('Razorpay Route', 'Cashfree Easy Split') DEFAULT 'Razorpay Route'");

        Schema::table('settlements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn('razorpay_transfer_id');
        });
    }
};
