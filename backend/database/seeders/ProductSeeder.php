<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\HsnCode;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public static array $products = [
        ['code' => 'p1', 'name' => 'Bosch GSB 600 RE Impact Drill', 'brand' => 'Bosch', 'cat' => 'tools', 'price' => 3499, 'mrp' => 4250, 'supplier' => 'Kumar Power Tools', 'eta' => 2, 'unit' => 'piece', 'tag' => 'Bestseller', 'hsn' => '8467',
            'specs' => [['Power', '600 W'], ['Chuck', '13 mm'], ['No-load Speed', '0–2800 rpm'], ['Warranty', '1 Year']],
            'desc' => 'Compact 600W impact drill for masonry, wood and steel. Variable-speed trigger with forward/reverse and a 13mm keyed chuck.'],
        ['code' => 'p2', 'name' => 'Havells FR PVC Wire 1.5sqmm (90m)', 'brand' => 'Havells', 'cat' => 'electrical', 'price' => 1180, 'mrp' => 1399, 'supplier' => 'Shree Electricals', 'eta' => 1, 'unit' => 'coil', 'tag' => 'Deal', 'hsn' => null,
            'specs' => [['Size', '1.5 sq mm'], ['Length', '90 m'], ['Insulation', 'FR PVC'], ['Voltage', '1100 V']],
            'desc' => 'Flame-retardant grade copper wire for domestic and commercial wiring. ISI marked, 90m coil.'],
        ['code' => 'p3', 'name' => 'Astral CPVC Pipe 1 inch (3m)', 'brand' => 'Astral', 'cat' => 'plumbing', 'price' => 420, 'mrp' => 520, 'supplier' => 'BuildMart Distributors', 'eta' => 3, 'unit' => 'piece', 'tag' => '', 'hsn' => '7307',
            'specs' => [['Diameter', '25 mm (1")'], ['Length', '3 m'], ['Grade', 'SDR 11'], ['Use', 'Hot & Cold']],
            'desc' => 'Lead-free CPVC pipe suitable for hot and cold water plumbing lines. Corrosion and scale resistant.'],
        ['code' => 'p4', 'name' => 'Asian Paints Apcolite Enamel 1L', 'brand' => 'Asian Paints', 'cat' => 'paint', 'price' => 385, 'mrp' => 440, 'supplier' => 'ColourHub Traders', 'eta' => 2, 'unit' => 'litre', 'tag' => '', 'hsn' => null,
            'specs' => [['Volume', '1 L'], ['Finish', 'High Gloss'], ['Coverage', '120–140 sqft'], ['Dry Time', '6–8 hrs']],
            'desc' => 'Premium synthetic enamel with a high-gloss finish for wood and metal surfaces. Excellent durability.'],
        ['code' => 'p5', 'name' => '3M H-700 Safety Helmet', 'brand' => '3M', 'cat' => 'safety', 'price' => 245, 'mrp' => 320, 'supplier' => 'SafeGuard Industrial', 'eta' => 2, 'unit' => 'piece', 'tag' => '', 'hsn' => null,
            'specs' => [['Material', 'HDPE Shell'], ['Standard', 'IS 2925'], ['Harness', 'Ratchet'], ['Colour', 'Yellow']],
            'desc' => 'Industrial safety helmet with ratchet harness and ventilation. IS 2925 certified for site use.'],
        ['code' => 'p6', 'name' => 'Fevicol SH Adhesive 5kg', 'brand' => 'Pidilite', 'cat' => 'adhesives', 'price' => 620, 'mrp' => 720, 'supplier' => 'Kumar Power Tools', 'eta' => 1, 'unit' => 'tub', 'tag' => 'Bestseller', 'hsn' => '3214',
            'specs' => [['Weight', '5 kg'], ['Type', 'Synthetic Resin'], ['Application', 'Wood/Laminate'], ['Set Time', '30 min']],
            'desc' => 'Trusted white woodworking adhesive with strong, durable bonding for furniture and laminates.'],
        ['code' => 'p7', 'name' => 'Hilti HST3 Anchor Bolt M10 (25 pc)', 'brand' => 'Hilti', 'cat' => 'fasteners', 'price' => 1290, 'mrp' => 1560, 'supplier' => 'FastFix Supplies', 'eta' => 4, 'unit' => 'box', 'tag' => '', 'hsn' => null,
            'specs' => [['Size', 'M10 × 90'], ['Material', 'Galvanised Steel'], ['Pack', '25 pcs'], ['Load', 'Heavy Duty']],
            'desc' => 'Heavy-duty mechanical expansion anchors for high-load concrete fixings. Approved for cracked concrete.'],
        ['code' => 'p8', 'name' => 'Cera Wall-Hung Wash Basin', 'brand' => 'Cera', 'cat' => 'sanitary', 'price' => 1850, 'mrp' => 2400, 'supplier' => 'BuildMart Distributors', 'eta' => 5, 'unit' => 'piece', 'tag' => '', 'hsn' => null,
            'specs' => [['Material', 'Vitreous China'], ['Type', 'Wall Hung'], ['Colour', 'Snow White'], ['Warranty', '5 Years']],
            'desc' => 'Sleek wall-hung wash basin in glossy snow-white vitreous china. Easy to clean and stain resistant.'],
        ['code' => 'p9', 'name' => 'Stanley Powerlock Tape 5m', 'brand' => 'Stanley', 'cat' => 'tools', 'price' => 260, 'mrp' => 310, 'supplier' => 'Kumar Power Tools', 'eta' => 1, 'unit' => 'piece', 'tag' => 'Deal', 'hsn' => '8205',
            'specs' => [['Length', '5 m'], ['Width', '19 mm'], ['Blade', 'Mylar Coated'], ['Lock', 'Power Lock']],
            'desc' => 'Durable measuring tape with Mylar-coated blade and sturdy power lock for one-handed use.'],
        ['code' => 'p10', 'name' => 'UltraTech Cement OPC 53 (50kg)', 'brand' => 'UltraTech', 'cat' => 'construction', 'price' => 395, 'mrp' => 430, 'supplier' => 'BuildMart Distributors', 'eta' => 3, 'unit' => 'bag', 'tag' => '', 'hsn' => '2523',
            'specs' => [['Grade', 'OPC 53'], ['Weight', '50 kg'], ['Standard', 'IS 12269'], ['Use', 'Structural']],
            'desc' => 'High-strength Ordinary Portland Cement for structural concrete. Consistent setting and gain of strength.'],
        ['code' => 'p11', 'name' => 'Crompton Brisk Air Exhaust Fan 6"', 'brand' => 'Crompton', 'cat' => 'electrical', 'price' => 1420, 'mrp' => 1750, 'supplier' => 'Shree Electricals', 'eta' => 2, 'unit' => 'piece', 'tag' => '', 'hsn' => null,
            'specs' => [['Sweep', '150 mm'], ['Speed', '2400 rpm'], ['Power', '40 W'], ['Warranty', '2 Years']],
            'desc' => 'Powerful exhaust fan with dust-resistant shutters for kitchens and washrooms. Low noise operation.'],
        ['code' => 'p12', 'name' => 'Karam Cut-Resistant Gloves (Pair)', 'brand' => 'Karam', 'cat' => 'safety', 'price' => 180, 'mrp' => 240, 'supplier' => 'SafeGuard Industrial', 'eta' => 1, 'unit' => 'pair', 'tag' => '', 'hsn' => null,
            'specs' => [['Cut Level', 'Level 5'], ['Material', 'HPPE'], ['Coating', 'PU Palm'], ['Size', 'Large']],
            'desc' => 'Cut-resistant industrial gloves with PU-coated palm for grip and protection during handling.'],
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'icon');
        $brands = Brand::pluck('id', 'name');
        $sellers = Seller::pluck('id', 'store_name');
        $hsnCodes = HsnCode::pluck('id', 'code');

        foreach (self::$products as $i => $p) {
            /** @var Product $product */
            $product = Product::updateOrCreate(
                ['product_code' => $p['code']],
                [
                    'sku' => 'SKU'.strtoupper(Str::random(6)),
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']).'-'.$p['code'],
                    'category_id' => $categories[$p['cat']],
                    'brand_id' => $brands[$p['brand']],
                    'seller_id' => $sellers[$p['supplier']],
                    'hsn_code_id' => $p['hsn'] ? ($hsnCodes[$p['hsn']] ?? null) : null,
                    'description' => $p['desc'],
                    'price' => $p['price'],
                    'mrp' => $p['mrp'],
                    'unit' => $p['unit'],
                    'stock' => random_int(30, 300),
                    'approval_status' => 'Approved',
                    'featured' => in_array($p['code'], ['p1', 'p6', 'p9']),
                    'sold_count' => random_int(150, 3000),
                    'eta_days' => $p['eta'],
                    'tag' => $p['tag'],
                ]
            );

            $product->specs()->delete();
            foreach ($p['specs'] as $j => $spec) {
                $product->specs()->create(['label' => $spec[0], 'value' => $spec[1], 'sort_order' => $j]);
            }
        }

        $this->seedExtraProducts($categories, $brands, $sellers);
    }

    private function seedExtraProducts($categories, $brands, $sellers): void
    {
        $extraNames = [
            'Cordless Drill Machine 18V', 'Heavy Duty Angle Grinder', 'Hammer Drill 13mm', 'Adjustable Pipe Wrench 14"',
            'Stainless Steel Hinges (Pack of 4)', 'PVC Ball Valve 1 inch', 'MCB Switch 32A', 'Copper Wire 2.5mm (90m)',
            'Galvanized Iron Screws (500pc)', 'Tower Bolt 8 inch', 'Cylindrical Door Lock', 'Acrylic Wall Putty 5kg',
            'Digital Vernier Caliper', 'Laser Distance Meter', 'Garden Pruning Shears', 'Anti-Theft Door Latch',
            'LED Flood Light 50W', 'Brass Gate Valve', 'Claw Hammer 16oz', 'Socket Wrench Set 40pc',
        ];

        $catIds = $categories->values()->all();
        $brandIds = $brands->values()->all();
        $sellerIds = $sellers->values()->all();

        foreach ($extraNames as $i => $name) {
            $price = random_int(150, 5000);
            Product::updateOrCreate(
                ['product_code' => 'ext-'.($i + 1)],
                [
                    'sku' => 'SKU'.strtoupper(Str::random(6)),
                    'name' => $name,
                    'slug' => Str::slug($name).'-ext-'.($i + 1),
                    'category_id' => fake()->randomElement($catIds),
                    'brand_id' => fake()->randomElement($brandIds),
                    'seller_id' => fake()->randomElement($sellerIds),
                    'description' => 'Reliable hardware supply for professional and home use.',
                    'price' => $price,
                    'mrp' => (int) round($price * (1 + random_int(5, 30) / 100)),
                    'unit' => 'piece',
                    'stock' => random_int(0, 400),
                    'approval_status' => fake()->randomElement(['Approved', 'Approved', 'Approved', 'Pending']),
                    'featured' => fake()->boolean(15),
                    'sold_count' => random_int(0, 2000),
                    'eta_days' => random_int(1, 5),
                    'tag' => fake()->randomElement(['', '', '', 'Deal']),
                ]
            );
        }
    }
}
