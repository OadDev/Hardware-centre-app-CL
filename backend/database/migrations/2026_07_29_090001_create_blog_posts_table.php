<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('excerpt');
            $table->longText('body');
            $table->string('author')->default('Hardware Centers Editorial');
            $table->unsignedInteger('read_minutes')->default(5);
            $table->boolean('featured')->default(false);
            $table->enum('status', ['Published', 'Draft'])->default('Published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
