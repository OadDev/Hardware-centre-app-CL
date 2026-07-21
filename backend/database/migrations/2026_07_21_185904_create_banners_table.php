<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('badge_label')->nullable();
            $table->enum('type', ['Homepage Banner', 'Offer Banner', 'Slider Image'])->default('Homepage Banner');
            $table->string('bg_color')->default('#1F2937');
            $table->string('image_path')->nullable();
            $table->string('link_type')->nullable();
            $table->string('link_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
