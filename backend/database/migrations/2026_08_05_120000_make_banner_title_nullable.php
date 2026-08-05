<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE banners MODIFY title VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE banners SET title = '' WHERE title IS NULL");
        DB::statement('ALTER TABLE banners MODIFY title VARCHAR(255) NOT NULL');
    }
};
