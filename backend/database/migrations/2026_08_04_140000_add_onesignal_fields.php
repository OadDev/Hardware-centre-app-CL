<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('onesignal_app_id')->nullable()->after('fast2sms_api_key');
            $table->text('onesignal_api_key')->nullable()->after('onesignal_app_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('onesignal_player_id')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['onesignal_app_id', 'onesignal_api_key']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('onesignal_player_id');
        });
    }
};
