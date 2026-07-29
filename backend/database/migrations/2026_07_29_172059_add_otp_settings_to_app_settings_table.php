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
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('otp_channel')->default('email')->after('smtp_password');
            $table->string('smtp_encryption')->nullable()->default('tls')->after('otp_channel');
            $table->string('smtp_from_address')->nullable()->after('smtp_encryption');
            $table->string('smtp_from_name')->nullable()->after('smtp_from_address');
            $table->text('fast2sms_api_key')->nullable()->after('smtp_from_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['otp_channel', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name', 'fast2sms_api_key']);
        });
    }
};
