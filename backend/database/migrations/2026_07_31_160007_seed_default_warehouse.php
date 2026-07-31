<?php

use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'WH-001'],
            [
                'name' => 'Hardware Centre Main Warehouse',
                'status' => 'Active',
            ]
        );
    }

    public function down(): void
    {
        Warehouse::where('code', 'WH-001')->delete();
    }
};
