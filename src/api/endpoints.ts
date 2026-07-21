import { http } from './client';
import {
  Address,
  CartResponse,
  Category,
  CategoryProducts,
  Customer,
  HomeData,
  Order,
  Product,
  ProductDetail,
  Supplier,
  Totals,
} from './types';

// Endpoints below either return the raw payload (when the controller nests a
// resource inside a plain response()->json([...]) array) or a Laravel
// resource "data" envelope (when the controller returns a Resource /
// ResourceCollection directly). Each function unwraps accordingly.

export const AuthApi = {
  requestOtp: (email: string) => http.post<{ message: string; debug_otp: string | null }>('/auth/otp/request', { email }),
  verifyOtp: (email: string, otp: string) =>
    http.post<{ token: string; customer: Customer }>('/auth/otp/verify', { email, otp }),
  guest: () => http.post<{ token: string; customer: Customer }>('/auth/guest'),
  me: async () => (await http.get<{ data: Customer }>('/me')).data,
  logout: () => http.post<{ message: string }>('/logout'),
};

export const HomeApi = {
  get: () => http.get<HomeData>('/home'),
};

export const CategoryApi = {
  list: async () => (await http.get<{ data: Category[] }>('/categories')).data,
  products: (icon: string, sort: string = 'pop') =>
    http.get<CategoryProducts>(`/categories/${encodeURIComponent(icon)}/products?sort=${sort}`),
};

export const ProductApi = {
  list: async (params: { q?: string; category?: string; sort?: string } = {}) => {
    const qs = new URLSearchParams();
    if (params.q) qs.set('q', params.q);
    if (params.category) qs.set('category', params.category);
    if (params.sort) qs.set('sort', params.sort);
    const suffix = qs.toString() ? `?${qs.toString()}` : '';
    return (await http.get<{ data: Product[] }>(`/products${suffix}`)).data;
  },
  show: (code: string) => http.get<ProductDetail>(`/products/${encodeURIComponent(code)}`),
};

export const SupplierApi = {
  list: async () => (await http.get<{ data: Supplier[] }>('/suppliers')).data,
};

export const CartApi = {
  get: () => http.get<CartResponse>('/cart'),
  add: (productCode: string, quantity: number = 1) =>
    http.post<CartResponse>('/cart/items', { product_code: productCode, quantity }),
  update: (productCode: string, quantity: number) =>
    http.patch<CartResponse>(`/cart/items/${encodeURIComponent(productCode)}`, { quantity }),
  remove: (productCode: string) => http.delete<CartResponse>(`/cart/items/${encodeURIComponent(productCode)}`),
};

export const WishlistApi = {
  list: async () => (await http.get<{ data: Product[] }>('/wishlist')).data,
  add: (productCode: string) => http.post<{ message: string }>(`/wishlist/${encodeURIComponent(productCode)}`),
  remove: (productCode: string) => http.delete<{ message: string }>(`/wishlist/${encodeURIComponent(productCode)}`),
};

export const AddressApi = {
  list: async () => (await http.get<{ data: Address[] }>('/addresses')).data,
  create: async (payload: {
    label?: string;
    name: string;
    line: string;
    city: string;
    state: string;
    pincode: string;
    is_default?: boolean;
  }) => (await http.post<{ data: Address }>('/addresses', payload)).data,
};

export const CouponApi = {
  apply: (code: string) => http.post<{ message: string; code: string; totals: Totals }>('/coupons/apply', { code }),
};

export const OrderApi = {
  list: async () => (await http.get<{ data: Order[] }>('/orders')).data,
  show: async (orderNo: string) => (await http.get<{ data: Order }>(`/orders/${encodeURIComponent(orderNo)}`)).data,
  create: async (payload: {
    address_id: number;
    shipping_method: 'standard' | 'express';
    payment_method: 'upi' | 'card' | 'netbanking' | 'cod';
    coupon_code?: string;
  }) => (await http.post<{ data: Order }>('/orders', payload)).data,
  reorder: (orderNo: string) => http.post<{ message: string }>(`/orders/${encodeURIComponent(orderNo)}/reorder`),
};
