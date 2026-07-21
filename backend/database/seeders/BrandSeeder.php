<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public static array $brands = [
        'Bosch', 'Havells', 'Astral', 'Asian Paints', '3M', 'Pidilite',
        'Hilti', 'Cera', 'Stanley', 'UltraTech', 'Crompton', 'Karam',
    ];

    public function run(): void
    {
        foreach (self::$brands as $i => $name) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'brand_code' => 'BRD-'.(200 + $i),
                    'name' => $name,
                    'featured' => in_array($name, ['Bosch', 'Havells', 'Stanley']),
                    'status' => 'Active',
                ]
            );
        }
    }
}
