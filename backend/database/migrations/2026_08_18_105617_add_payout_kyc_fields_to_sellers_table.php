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
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('pan_number')->nullable()->after('gst_number');
            $table->string('bank_account_holder')->nullable()->after('pan_number');
            $table->text('bank_account_number')->nullable()->after('bank_account_holder');
            $table->string('bank_ifsc')->nullable()->after('bank_account_number');
            $table->string('razorpay_account_id')->nullable()->after('bank_ifsc');
            $table->string('razorpay_account_status')->default('Not Linked')->after('razorpay_account_id');
            $table->text('razorpay_account_error')->nullable()->after('razorpay_account_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'pan_number', 'bank_account_holder', 'bank_account_number', 'bank_ifsc',
                'razorpay_account_id', 'razorpay_account_status', 'razorpay_account_error',
            ]);
        });
    }
};
