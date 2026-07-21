<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['title' => 'Up to 30% off Plumbing', 'subtitle' => 'Pipes, fittings & sanitaryware', 'badge_label' => 'MONSOON SALE', 'bg_color' => '#1F2937', 'sort_order' => 0],
            ['title' => 'Bulk orders, better rates', 'subtitle' => 'Save more on 10+ quantities', 'badge_label' => 'TRADE PRICES', 'bg_color' => '#7C2D12', 'sort_order' => 1],
            ['title' => 'Bosch • Stanley • Hilti', 'subtitle' => 'Flat deals on top brands', 'badge_label' => 'POWER TOOLS FEST', 'bg_color' => '#374151', 'sort_order' => 2],
        ];

        foreach ($banners as $row) {
            Banner::updateOrCreate(
                ['title' => $row['title']],
                $row + ['type' => 'Homepage Banner', 'status' => 'Active']
            );
        }
    }
}
