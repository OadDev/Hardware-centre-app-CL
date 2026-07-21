<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsn_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->decimal('gst_rate', 5, 2)->default(18);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsn_codes');
    }
};
