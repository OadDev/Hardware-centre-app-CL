<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'how-to-choose-the-right-power-drill',
                'title' => 'How to Choose the Right Power Drill for Your Job',
                'category' => 'Buying Guide',
                'excerpt' => 'A practical breakdown of corded vs cordless, torque ratings, and chuck sizes to help you pick the right drill.',
                'body' => "<p>A practical breakdown of corded vs cordless, torque ratings, and chuck sizes to help you pick the right drill.</p>"
                    ."<h3>Corded vs. cordless: what actually matters</h3>"
                    ."<p>For job sites without reliable power access, a cordless drill with an 18V+ battery gives you the freedom to move fast — but corded drills still win on sustained torque for masonry and heavy steel work. If you're drilling into concrete or brick more than a few times a week, keep a corded hammer drill in the kit alongside a cordless driver for everyday fastening.</p>"
                    ."<h3>Reading the torque and chuck specs</h3>"
                    ."<p>Look past the wattage number on the box. Torque (measured in Nm) tells you how much resistance the drill can push through, while chuck size (10mm vs 13mm) determines the largest bit you can use. For general household and light contractor work, a 13mm chuck with 20+ Nm of torque covers almost everything.</p>"
                    ."<blockquote>Pro tip: Buy a drill with a keyless chuck — it saves real time when you're switching bits multiple times an hour on site.</blockquote>"
                    ."<h3>Our recommendation</h3>"
                    ."<p>For most contractors, a mid-range impact drill strikes the right balance of torque, price and chuck size — and it's currently one of our best-selling power tools on Hardware Centers.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 5,
                'featured' => true,
                'published_at' => '2026-06-12 09:00:00',
            ],
            [
                'slug' => 'electrical-safety-standards-every-contractor-should-know',
                'title' => '5 Electrical Safety Standards Every Contractor Should Know',
                'category' => 'Trends',
                'excerpt' => 'Stay compliant and protect your crew with these essential wiring and installation safety practices.',
                'body' => "<p>Stay compliant and protect your crew with these essential wiring and installation safety practices.</p>"
                    ."<h3>Use the right insulation grade</h3>"
                    ."<p>FR (flame-retardant) and FRLS (flame-retardant low-smoke) rated wires are now standard for residential and commercial wiring in India. Always match the insulation grade to the installation environment.</p>"
                    ."<h3>Size your MCBs correctly</h3>"
                    ."<p>An undersized MCB trips constantly; an oversized one won't protect the circuit. Match breaker ratings to the wire gauge and expected load before signing off any installation.</p>"
                    ."<h3>Earth every circuit</h3>"
                    ."<p>Proper earthing isn't optional — it's the difference between a nuisance trip and a fire hazard. Test continuity on every earth connection before energizing a new circuit.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 6,
                'featured' => false,
                'published_at' => '2026-05-28 09:00:00',
            ],
            [
                'slug' => 'indias-fastest-growing-hardware-supply-chains',
                'title' => "Inside India's Fastest Growing Hardware Supply Chains",
                'category' => 'Supplier Spotlight',
                'excerpt' => 'How GST-verified suppliers on Hardware Centers are shipping to 500+ cities within 48 hours.',
                'body' => "<p>How GST-verified suppliers on Hardware Centers are shipping to 500+ cities within 48 hours.</p>"
                    ."<h3>Regional warehousing, not just a single hub</h3>"
                    ."<p>The fastest-growing sellers on the platform run 2–3 regional warehouses rather than one central store, cutting last-mile delivery time by nearly half for tier-2 and tier-3 cities.</p>"
                    ."<h3>What buyers should look for</h3>"
                    ."<p>GST verification, a consistent rating above 4.5, and clear stock-availability signals are the strongest predictors of a reliable supplier relationship on Hardware Centers.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 4,
                'featured' => false,
                'published_at' => '2026-05-14 09:00:00',
            ],
            [
                'slug' => 'interior-vs-exterior-paint-difference',
                'title' => "Interior vs Exterior Paint: What's the Real Difference?",
                'category' => 'Buying Guide',
                'excerpt' => 'Weather resistance, sheen, and durability — everything you need before your next paint order.',
                'body' => "<p>Weather resistance, sheen, and durability — everything you need before your next paint order.</p>"
                    ."<h3>Why you can't swap one for the other</h3>"
                    ."<p>Exterior paints are formulated with UV-resistant resins and flexible binders to survive temperature swings and monsoon moisture. Using an interior emulsion outdoors will chalk and peel within a season.</p>"
                    ."<h3>Sheen levels, explained</h3>"
                    ."<p>Matte finishes hide wall imperfections well and suit interior ceilings and living rooms; higher-sheen exterior finishes shed dirt and rain more effectively, which is why most exteriors use satin or gloss.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 5,
                'featured' => false,
                'published_at' => '2026-05-02 09:00:00',
            ],
            [
                'slug' => 'sellers-checklist-before-listing-on-b2b-marketplace',
                'title' => "A Seller's Checklist Before Listing on a B2B Marketplace",
                'category' => 'Business',
                'excerpt' => 'GST documentation, catalog photography, and pricing tips to get your store live faster.',
                'body' => "<p>GST documentation, catalog photography, and pricing tips to get your store live faster.</p>"
                    ."<h3>Documents to keep ready</h3>"
                    ."<p>Your GST certificate, PAN, and a cancelled cheque for payouts are the three documents that most often delay seller verification. Have scanned copies ready before you start the application.</p>"
                    ."<h3>Photograph for scale, not just angle</h3>"
                    ."<p>Buyers convert better when product photos show scale references (a hand, a ruler, a common object) alongside the studio shot — especially for hardware where dimensions matter more than aesthetics.</p>"
                    ."<h3>Price for the trade, not retail</h3>"
                    ."<p>List trade/wholesale pricing up front. Marketplace buyers are comparing you against other verified sellers in real time, and hidden 'call for bulk pricing' listings convert far worse.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 7,
                'featured' => false,
                'published_at' => '2026-04-21 09:00:00',
            ],
            [
                'slug' => 'why-cpvc-is-replacing-gi-pipes',
                'title' => 'Why CPVC Is Replacing GI Pipes on Indian Job Sites',
                'category' => 'Trends',
                'excerpt' => 'Corrosion resistance, cost, and installation speed are reshaping plumbing specs nationwide.',
                'body' => "<p>Corrosion resistance, cost, and installation speed are reshaping plumbing specs nationwide.</p>"
                    ."<h3>No more corrosion callbacks</h3>"
                    ."<p>GI pipes corrode from the inside out over 8–10 years, discoloring water and eventually leaking at threaded joints. CPVC doesn't rust, so it's become the default spec for new residential plumbing.</p>"
                    ."<h3>Faster installs, fewer tools</h3>"
                    ."<p>Solvent-cement CPVC joints cure in minutes without threading or welding equipment, cutting typical plumbing labor time by 30–40% compared to GI on a comparable job.</p>",
                'author' => 'Hardware Centers Editorial',
                'read_minutes' => 4,
                'featured' => false,
                'published_at' => '2026-04-09 09:00:00',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(['slug' => $post['slug']], [...$post, 'status' => 'Published']);
        }
    }
}
