export type ProductVariant = {
  id: string;
  sku: string;
  attributes: Record<string, string>;
  label: string;
  price: number;
  stock: number;
};

export type ProductPriceTier = {
  min_qty: number;
  price: number;
};

export type Product = {
  id: string;
  sku: string;
  name: string;
  brand: string;
  cat: string;
  category_name: string;
  price: number;
  mrp: number;
  discount_pct: number;
  rating: number;
  reviews: number;
  supplier: string;
  seller_id: string;
  seller_rating: number;
  seller_years: number;
  loc: string;
  eta: number;
  unit: string;
  tag: '' | 'Bestseller' | 'Deal';
  stock: number;
  stock_status: string;
  featured: boolean;
  wished: boolean;
  specs: [string, string][];
  desc: string;
  images: string[];
  video_url: string | null;
  variants: ProductVariant[];
  price_tiers: ProductPriceTier[];
};

export type Category = {
  id: string;
  name: string;
  parent_id: string | null;
  count: number;
  status: string;
};

export type Banner = {
  id: number;
  badge: string;
  title: string;
  sub: string;
  bg: string;
  image_url: string | null;
  type: string;
  status: string;
};

export type Supplier = {
  id: string;
  name: string;
  owner_name: string;
  email: string;
  phone: string | null;
  loc: string;
  state: string;
  gst: string | null;
  rating: number;
  years: number;
};

export type Address = {
  id: number;
  label: string;
  name: string;
  line: string;
  city: string;
  state: string;
  pincode: string;
  is_default: boolean;
};

export type Customer = {
  id: string;
  name: string;
  email: string;
  phone: string | null;
  business_name: string | null;
  gst: string | null;
  city: string | null;
  state: string | null;
  wallet_balance: number;
  status: string;
  is_guest: boolean;
  orders_count: number;
  wishlist_count: number;
};

export type OrderStatus = 'ongoing' | 'delivered' | 'cancelled' | 'returned';

export type OrderItem = { id: string; q: number; name: string; brand: string; variant: string | null };

export type TimelineStep = { title: string; time: string; state: 'done' | 'current' | 'pending' };

export type Order = {
  no: string;
  status: OrderStatus;
  stage: string;
  date: string;
  sub: string;
  items: OrderItem[];
  subtotal: number;
  discount: number;
  gst: number;
  delivery: number;
  total: number;
  payment_method: 'upi' | 'card' | 'netbanking' | 'cod';
  payment_status: string;
  shipping_method: 'standard' | 'express';
  timeline: TimelineStep[];
};

export type Totals = {
  sub: number;
  disc: number;
  gst: number;
  deliv: number;
  total: number;
  count: number;
};

export type CartItemVariant = {
  id: string;
  label: string;
  price: number;
  stock: number;
};

export type CartItem = {
  product: Product;
  variant: CartItemVariant | null;
  quantity: number;
  line_total: number;
};

export type CartResponse = {
  items: CartItem[];
  totals: Totals;
};

export type Review = {
  name: string;
  init: string;
  date: string;
  stars: number;
  text: string;
};

export type RatingBar = { stars: number; pct: number };

export type ProductDetail = {
  product: Product;
  reviews: Review[];
  rating_breakdown: RatingBar[];
};

export type HomeData = {
  banners: Banner[];
  categories: Category[];
  popular: Product[];
  deals: Product[];
  trending: Product[];
  recommended: Product[];
  suppliers: Supplier[];
  brands: string[];
};

export type CategoryProducts = {
  category: Category;
  products: Product[];
};

export type OnboardingSlide = {
  id: number;
  title: string;
  subtitle: string | null;
  image_url: string | null;
};
