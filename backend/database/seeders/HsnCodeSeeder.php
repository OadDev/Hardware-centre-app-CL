<?php

namespace Database\Seeders;

use App\Models\HsnCode;
use Illuminate\Database\Seeder;

class HsnCodeSeeder extends Seeder
{
    public static array $codes = [
        ['code' => '8467', 'description' => 'Power-operated hand tools', 'gst_rate' => 18],
        ['code' => '8205', 'description' => 'Hand tools (hammers, pliers etc.)', 'gst_rate' => 18],
        ['code' => '7307', 'description' => 'Pipe fittings of iron/steel', 'gst_rate' => 18],
        ['code' => '8301', 'description' => 'Locks and padlocks', 'gst_rate' => 18],
        ['code' => '3214', 'description' => 'Putty, sealants, adhesives', 'gst_rate' => 18],
        ['code' => '8302', 'description' => 'Door & window hardware fittings', 'gst_rate' => 18],
        ['code' => '2523', 'description' => 'Cement', 'gst_rate' => 28],
    ];

    public function run(): void
    {
        foreach (self::$codes as $row) {
            HsnCode::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
