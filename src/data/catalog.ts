export type Category = {
  id: string;
  name: string;
  count: number;
};

export type Product = {
  id: string;
  name: string;
  brand: string;
  cat: string;
  price: number;
  mrp: number;
  rating: number;
  reviews: number;
  supplier: string;
  loc: string;
  eta: number;
  unit: string;
  tag: '' | 'Bestseller' | 'Deal';
  specs: [string, string][];
  desc: string;
};

export type Supplier = {
  name: string;
  loc: string;
  rating: number;
  years: number;
  verified: boolean;
};

export type Banner = {
  id: string;
  badge: string;
  title: string;
  sub: string;
  bg: string;
};

export const CATS: Category[] = [
  { id: 'electrical', name: 'Electrical', count: 1240 },
  { id: 'plumbing', name: 'Plumbing', count: 860 },
  { id: 'paint', name: 'Paint', count: 540 },
  { id: 'tools', name: 'Tools', count: 1580 },
  { id: 'fasteners', name: 'Fasteners', count: 920 },
  { id: 'safety', name: 'Safety', count: 410 },
  { id: 'construction', name: 'Construction', count: 730 },
  { id: 'adhesives', name: 'Adhesives', count: 260 },
  { id: 'sanitary', name: 'Sanitary', count: 380 },
  { id: 'agriculture', name: 'Agriculture', count: 190 },
  { id: 'industrial', name: 'Industrial', count: 640 },
  { id: 'more', name: 'More', count: 0 },
];

export const PRODUCTS: Product[] = [
  { id: 'p1', name: 'Bosch GSB 600 RE Impact Drill', brand: 'Bosch', cat: 'tools', price: 3499, mrp: 4250, rating: 4.6, reviews: 1284, supplier: 'Kumar Power Tools', loc: 'New Delhi', eta: 2, unit: 'piece', tag: 'Bestseller', specs: [['Power', '600 W'], ['Chuck', '13 mm'], ['No-load Speed', '0–2800 rpm'], ['Warranty', '1 Year']], desc: 'Compact 600W impact drill for masonry, wood and steel. Variable-speed trigger with forward/reverse and a 13mm keyed chuck.' },
  { id: 'p2', name: 'Havells FR PVC Wire 1.5sqmm (90m)', brand: 'Havells', cat: 'electrical', price: 1180, mrp: 1399, rating: 4.7, reviews: 932, supplier: 'Shree Electricals', loc: 'Mumbai', eta: 1, unit: 'coil', tag: 'Deal', specs: [['Size', '1.5 sq mm'], ['Length', '90 m'], ['Insulation', 'FR PVC'], ['Voltage', '1100 V']], desc: 'Flame-retardant grade copper wire for domestic and commercial wiring. ISI marked, 90m coil.' },
  { id: 'p3', name: 'Astral CPVC Pipe 1 inch (3m)', brand: 'Astral', cat: 'plumbing', price: 420, mrp: 520, rating: 4.5, reviews: 418, supplier: 'BuildMart Distributors', loc: 'Pune', eta: 3, unit: 'piece', tag: '', specs: [['Diameter', '25 mm (1")'], ['Length', '3 m'], ['Grade', 'SDR 11'], ['Use', 'Hot & Cold']], desc: 'Lead-free CPVC pipe suitable for hot and cold water plumbing lines. Corrosion and scale resistant.' },
  { id: 'p4', name: 'Asian Paints Apcolite Enamel 1L', brand: 'Asian Paints', cat: 'paint', price: 385, mrp: 440, rating: 4.4, reviews: 612, supplier: 'ColourHub Traders', loc: 'Ahmedabad', eta: 2, unit: 'litre', tag: '', specs: [['Volume', '1 L'], ['Finish', 'High Gloss'], ['Coverage', '120–140 sqft'], ['Dry Time', '6–8 hrs']], desc: 'Premium synthetic enamel with a high-gloss finish for wood and metal surfaces. Excellent durability.' },
  { id: 'p5', name: '3M H-700 Safety Helmet', brand: '3M', cat: 'safety', price: 245, mrp: 320, rating: 4.6, reviews: 287, supplier: 'SafeGuard Industrial', loc: 'Chennai', eta: 2, unit: 'piece', tag: '', specs: [['Material', 'HDPE Shell'], ['Standard', 'IS 2925'], ['Harness', 'Ratchet'], ['Colour', 'Yellow']], desc: 'Industrial safety helmet with ratchet harness and ventilation. IS 2925 certified for site use.' },
  { id: 'p6', name: 'Fevicol SH Adhesive 5kg', brand: 'Pidilite', cat: 'adhesives', price: 620, mrp: 720, rating: 4.8, reviews: 1543, supplier: 'Kumar Power Tools', loc: 'New Delhi', eta: 1, unit: 'tub', tag: 'Bestseller', specs: [['Weight', '5 kg'], ['Type', 'Synthetic Resin'], ['Application', 'Wood/Laminate'], ['Set Time', '30 min']], desc: 'Trusted white woodworking adhesive with strong, durable bonding for furniture and laminates.' },
  { id: 'p7', name: 'Hilti HST3 Anchor Bolt M10 (25 pc)', brand: 'Hilti', cat: 'fasteners', price: 1290, mrp: 1560, rating: 4.7, reviews: 164, supplier: 'FastFix Supplies', loc: 'Pune', eta: 4, unit: 'box', tag: '', specs: [['Size', 'M10 × 90'], ['Material', 'Galvanised Steel'], ['Pack', '25 pcs'], ['Load', 'Heavy Duty']], desc: 'Heavy-duty mechanical expansion anchors for high-load concrete fixings. Approved for cracked concrete.' },
  { id: 'p8', name: 'Cera Wall-Hung Wash Basin', brand: 'Cera', cat: 'sanitary', price: 1850, mrp: 2400, rating: 4.3, reviews: 209, supplier: 'BuildMart Distributors', loc: 'Pune', eta: 5, unit: 'piece', tag: '', specs: [['Material', 'Vitreous China'], ['Type', 'Wall Hung'], ['Colour', 'Snow White'], ['Warranty', '5 Years']], desc: 'Sleek wall-hung wash basin in glossy snow-white vitreous china. Easy to clean and stain resistant.' },
  { id: 'p9', name: 'Stanley Powerlock Tape 5m', brand: 'Stanley', cat: 'tools', price: 260, mrp: 310, rating: 4.7, reviews: 876, supplier: 'Kumar Power Tools', loc: 'New Delhi', eta: 1, unit: 'piece', tag: 'Deal', specs: [['Length', '5 m'], ['Width', '19 mm'], ['Blade', 'Mylar Coated'], ['Lock', 'Power Lock']], desc: 'Durable measuring tape with Mylar-coated blade and sturdy power lock for one-handed use.' },
  { id: 'p10', name: 'UltraTech Cement OPC 53 (50kg)', brand: 'UltraTech', cat: 'construction', price: 395, mrp: 430, rating: 4.5, reviews: 2210, supplier: 'BuildMart Distributors', loc: 'Pune', eta: 3, unit: 'bag', tag: '', specs: [['Grade', 'OPC 53'], ['Weight', '50 kg'], ['Standard', 'IS 12269'], ['Use', 'Structural']], desc: 'High-strength Ordinary Portland Cement for structural concrete. Consistent setting and gain of strength.' },
  { id: 'p11', name: 'Crompton Brisk Air Exhaust Fan 6"', brand: 'Crompton', cat: 'electrical', price: 1420, mrp: 1750, rating: 4.4, reviews: 531, supplier: 'Shree Electricals', loc: 'Mumbai', eta: 2, unit: 'piece', tag: '', specs: [['Sweep', '150 mm'], ['Speed', '2400 rpm'], ['Power', '40 W'], ['Warranty', '2 Years']], desc: 'Powerful exhaust fan with dust-resistant shutters for kitchens and washrooms. Low noise operation.' },
  { id: 'p12', name: 'Karam Cut-Resistant Gloves (Pair)', brand: 'Karam', cat: 'safety', price: 180, mrp: 240, rating: 4.5, reviews: 143, supplier: 'SafeGuard Industrial', loc: 'Chennai', eta: 1, unit: 'pair', tag: '', specs: [['Cut Level', 'Level 5'], ['Material', 'HPPE'], ['Coating', 'PU Palm'], ['Size', 'Large']], desc: 'Cut-resistant industrial gloves with PU-coated palm for grip and protection during handling.' },
];

export const BANNERS: Banner[] = [
  { id: 'banner-1', badge: 'MONSOON SALE', title: 'Up to 30% off Plumbing', sub: 'Pipes, fittings & sanitaryware', bg: '#1F2937' },
  { id: 'banner-2', badge: 'TRADE PRICES', title: 'Bulk orders, better rates', sub: 'Save more on 10+ quantities', bg: '#7C2D12' },
  { id: 'banner-3', badge: 'POWER TOOLS FEST', title: 'Bosch • Stanley • Hilti', sub: 'Flat deals on top brands', bg: '#374151' },
];

export const BRANDS = ['Bosch', 'Havells', 'Asian Paints', 'Stanley', '3M', 'Pidilite', 'Astral', 'Crompton'];

export const SUPPLIERS: Supplier[] = [
  { name: 'Kumar Power Tools', loc: 'New Delhi', rating: 4.7, years: 12, verified: true },
  { name: 'Shree Electricals', loc: 'Mumbai', rating: 4.6, years: 9, verified: true },
  { name: 'BuildMart Distributors', loc: 'Pune', rating: 4.5, years: 15, verified: true },
];

export const REVIEWS = [
  { name: 'Amit Sharma', init: 'A', date: '2 weeks ago', stars: 5, text: 'Genuine product, delivered on time. Packaging was solid and price was better than my local market.' },
  { name: 'Priya Verma', init: 'P', date: '1 month ago', stars: 4, text: 'Good quality and works as described. Would order again for my next site project.' },
];

export const HOME_SECTIONS = {
  popular: ['p1', 'p2', 'p6', 'p9'],
  deals: ['p2', 'p9', 'p3', 'p5'],
  trending: ['p6', 'p10', 'p11', 'p7'],
  recommended: ['p5', 'p8', 'p12', 'p4'],
  recent: ['p9', 'p3', 'p11'],
};

export const COUPONS: Record<string, number> = { HC100: 100, HC500: 500, WELCOME150: 150 };

export function findProduct(id: string): Product {
  return PRODUCTS.find((p) => p.id === id) || PRODUCTS[0];
}

export function findSupplier(name: string): Supplier {
  return SUPPLIERS.find((s) => s.name === name) || SUPPLIERS[0];
}

export function catName(id: string | null): string {
  if (!id) return 'All Products';
  const c = CATS.find((x) => x.id === id);
  return c ? c.name : 'All Products';
}

export function byIds(ids: string[]): Product[] {
  return ids.map(findProduct);
}
