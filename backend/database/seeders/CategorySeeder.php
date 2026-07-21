<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public static array $categories = [
        ['id' => 'electrical', 'name' => 'Electrical', 'count' => 1240],
        ['id' => 'plumbing', 'name' => 'Plumbing', 'count' => 860],
        ['id' => 'paint', 'name' => 'Paint', 'count' => 540],
        ['id' => 'tools', 'name' => 'Tools', 'count' => 1580],
        ['id' => 'fasteners', 'name' => 'Fasteners', 'count' => 920],
        ['id' => 'safety', 'name' => 'Safety', 'count' => 410],
        ['id' => 'construction', 'name' => 'Construction', 'count' => 730],
        ['id' => 'adhesives', 'name' => 'Adhesives', 'count' => 260],
        ['id' => 'sanitary', 'name' => 'Sanitary', 'count' => 380],
        ['id' => 'agriculture', 'name' => 'Agriculture', 'count' => 190],
        ['id' => 'industrial', 'name' => 'Industrial', 'count' => 640],
    ];

    public function run(): void
    {
        foreach (self::$categories as $i => $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['id']],
                [
                    'category_code' => 'CAT-'.(100 + $i),
                    'name' => $cat['name'],
                    'icon' => $cat['id'],
                    'sort_order' => $i,
                    'status' => 'Active',
                ]
            );
        }
    }
}
