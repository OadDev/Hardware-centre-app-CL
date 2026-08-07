<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('fast2sms_sender_id')->nullable()->after('fast2sms_api_key');
            $table->string('fast2sms_dlt_template_id')->nullable()->after('fast2sms_sender_id');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['fast2sms_sender_id', 'fast2sms_dlt_template_id']);
        });
    }
};
