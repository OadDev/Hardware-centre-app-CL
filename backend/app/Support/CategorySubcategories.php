<?php

namespace App\Support;

class CategorySubcategories
{
    private const MAP = [
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
        'more' => [],
    ];

    /** @return string[] */
    public static function forIcon(string $icon, ?int $limit = null): array
    {
        $items = self::MAP[$icon] ?? [];

        return $limit ? array_slice($items, 0, $limit) : $items;
    }
}
