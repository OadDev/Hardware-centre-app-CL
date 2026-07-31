<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The exact subcategory names the website already displayed as static,
     * non-clickable text (App\Support\CategorySubcategories). This turns
     * them into real Category rows so they can be assigned to products,
     * browsed, and given their own commission rates.
     */
    private const SUBCATEGORIES = [
        'electrical' => ['Wires & Cables', 'Switches & Sockets', 'MCBs & Distribution Boards', 'LED Lighting', 'Ceiling & Exhaust Fans', 'Conduits & Casing Pipes', 'Extension Boards', 'Insulation Tape'],
        'plumbing' => ['CPVC & PVC Pipes', 'Taps & Faucets', 'Valves & Ball Cocks', 'Water Storage Tanks', 'Pumps & Motors', 'Bathroom Fittings', 'Pipe Fitting Tools', 'Flexible Hoses'],
        'paint' => ['Interior Emulsions', 'Exterior Weatherproof', 'Wood Finishes & Polish', 'Primers & Undercoats', 'Brushes & Rollers', 'Waterproofing Compounds', 'Wall Putty & Fillers', 'Spray Equipment'],
        'tools' => ['Power Drills & Drivers', 'Angle Grinders', 'Hand Tool Sets', 'Measuring Tools', 'Tool Kits & Storage', 'Welding Equipment', 'Garden Tools', 'Bench & Table Tools'],
        'fasteners' => ['Screws & Bolts', 'Nuts & Washers', 'Nails & Pins', 'Anchors & Rawl Plugs', 'Rivets & Studs', 'Threaded Rods', 'Washers & Clips', 'Fastening Kits'],
        'safety' => ['Safety Helmets', 'Hand Gloves', 'Safety Shoes', 'Eye & Face Protection', 'Reflective Jackets', 'Ear Protection', 'Safety Harnesses', 'First Aid Kits'],
        'construction' => ['Cement & Concrete', 'Bricks & Blocks', 'Steel & TMT Bars', 'Sand & Aggregates', 'Waterproofing', 'Formwork & Scaffolding', 'Tiles & Flooring', 'Roofing Sheets'],
        'adhesives' => ['Construction Adhesives', 'Silicone Sealants', 'Epoxy & Resins', 'Industrial Tapes', 'Wood Glue', 'Contact Cement', 'Grouts & Fillers', 'Foam Sealants'],
        'sanitary' => ['Wash Basins', 'Toilets & Commodes', 'Bathroom Accessories', 'Kitchen Sinks', 'Faucet Fittings', 'Shower Systems', 'Bath Fittings', 'Drainage Fittings'],
        'agriculture' => ['Irrigation Pipes & Fittings', 'Sprayers & Pumps', 'Hand Tools', 'Greenhouse Supplies', 'Fencing & Netting', 'Seeds & Fertilizer Tools', 'Storage & Silos', 'Farm Safety Gear'],
        'industrial' => ['Bearings & Belts', 'Motors & Drives', 'Industrial Fasteners', 'Safety Signage', 'Material Handling', 'Lubricants & Greases', 'Welding Supplies', 'Measuring Instruments'],
    ];

    public function up(): void
    {
        $nextCode = 100 + Category::count();

        foreach (self::SUBCATEGORIES as $parentSlug => $names) {
            $parent = Category::where('slug', $parentSlug)->first();
            if (! $parent) {
                continue;
            }

            foreach ($names as $i => $name) {
                $slug = Str::slug($parentSlug.'-'.$name);

                if (Category::where('slug', $slug)->exists()) {
                    continue;
                }

                // Live admin usage since setup may have already claimed some
                // 'CAT-n' codes ahead of this count-based guess, so scan
                // forward to a code that isn't taken rather than assuming
                // the count reflects an unbroken sequence.
                while (Category::where('category_code', 'CAT-'.$nextCode)->exists()) {
                    $nextCode++;
                }

                Category::create([
                    'parent_id' => $parent->id,
                    'category_code' => 'CAT-'.$nextCode,
                    'name' => $name,
                    'slug' => $slug,
                    'icon' => $slug,
                    'sort_order' => $i,
                    'status' => 'Active',
                ]);
                $nextCode++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Category::whereNotNull('parent_id')->delete();
    }
};
