<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('Hardware Centers Pvt. Ltd.');
            $table->string('contact_number')->nullable();
            $table->string('support_email')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('invoice_prefix')->default('HC-INV-');
            $table->string('currency')->default('INR (₹)');
            $table->string('timezone')->default('Asia/Kolkata (IST)');
            $table->string('language')->default('English');
            $table->text('registered_address')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('ga_id')->nullable();
            $table->string('gmaps_key')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->text('fb_capi_token')->nullable();
            $table->decimal('default_gst_rate', 5, 2)->default(18);
            $table->boolean('gst_inclusive')->default(true);
            $table->unsignedInteger('password_min_length')->default(8);
            $table->boolean('password_require_special')->default(true);
            $table->boolean('require_2fa_admins')->default(false);
            $table->unsignedInteger('session_timeout_minutes')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
