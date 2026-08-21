import { http } from './client';
import {
  Address,
  CartResponse,
  Category,
  CategoryProducts,
  Customer,
  HomeData,
  OnboardingSlide,
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

export type OtpChannel = 'email' | 'sms';

export const AuthApi = {
  getOtpChannel: () => http.get<{ channel: OtpChannel }>('/auth/otp/channel'),
  requestOtp: (channel: OtpChannel, contact: string) =>
    http.post<{ message: string; channel: OtpChannel; debug_otp: string | null }>('/auth/otp/request', {
      channel,
      [channel === 'sms' ? 'phone' : 'email']: contact,
    }),
  verifyOtp: (channel: OtpChannel, contact: string, otp: string) =>
    http.post<{ token: string; customer: Customer }>('/auth/otp/verify', {
      channel,
      [channel === 'sms' ? 'phone' : 'email']: contact,
      otp,
    }),
  guest: () => http.post<{ token: string; customer: Customer }>('/auth/guest'),
  me: async () => (await http.get<{ data: Customer }>('/me')).data,
  updateMe: async (payload: { name: string; business_name?: string; city?: string; state?: string }) =>
    (await http.patch<{ data: Customer }>('/me', payload)).data,
  logout: () => http.post<{ message: string }>('/logout'),
};

export const PushApi = {
  register: (playerId: string) => http.post<{ message: string }>('/push/register', { player_id: playerId }),
  unregister: () => http.post<{ message: string }>('/push/unregister'),
};

export const HomeApi = {
  get: () => http.get<HomeData>('/home'),
};

export const OnboardingApi = {
  list: async () => (await http.get<{ slides: OnboardingSlide[] }>('/onboarding')).slides,
};

export const CategoryApi = {
  list: async () => (await http.get<{ data: Category[] }>('/categories')).data,
  products: (icon: string, sort: string = 'pop') =>
    http.get<CategoryProducts>(`/categories/${encodeURIComponent(icon)}/products?sort=${sort}`),
};

export const ProductApi = {
  list: async (params: { q?: string; category?: string; sort?: string; supplier?: string } = {}) => {
    const qs = new URLSearchParams();
    if (params.q) qs.set('q', params.q);
    if (params.category) qs.set('category', params.category);
    if (params.sort) qs.set('sort', params.sort);
    if (params.supplier) qs.set('supplier', params.supplier);
    const suffix = qs.toString() ? `?${qs.toString()}` : '';
    return (await http.get<{ data: Product[] }>(`/products${suffix}`)).data;
  },
  show: (code: string) => http.get<ProductDetail>(`/products/${encodeURIComponent(code)}`),
};

export const SupplierApi = {
  list: async () => (await http.get<{ data: Supplier[] }>('/suppliers')).data,
};

export const CartApi = {
  get: (params: { paymentMethod?: string; shippingMethod?: string; addressId?: number | null } = {}) => {
    const qs = new URLSearchParams();
    if (params.paymentMethod) qs.set('payment_method', params.paymentMethod);
    if (params.shippingMethod) qs.set('shipping_method', params.shippingMethod);
    if (params.addressId) qs.set('address_id', String(params.addressId));
    const suffix = qs.toString() ? `?${qs.toString()}` : '';
    return http.get<CartResponse>(`/cart${suffix}`);
  },
  add: (productCode: string, quantity: number = 1, variantCode?: string) =>
    http.post<CartResponse>('/cart/items', { product_code: productCode, variant_code: variantCode, quantity }),
  update: (productCode: string, quantity: number, variantCode?: string) =>
    http.patch<CartResponse>(
      `/cart/items/${encodeURIComponent(productCode)}${variantCode ? `?variant=${encodeURIComponent(variantCode)}` : ''}`,
      { quantity }
    ),
  remove: (productCode: string, variantCode?: string) =>
    http.delete<CartResponse>(
      `/cart/items/${encodeURIComponent(productCode)}${variantCode ? `?variant=${encodeURIComponent(variantCode)}` : ''}`
    ),
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
    gst_number?: string;
    email?: string;
    is_default?: boolean;
  }) => (await http.post<{ data: Address }>('/addresses', payload)).data,
};

export const CouponApi = {
  apply: (code: string, paymentMethod?: string, shippingMethod?: string, addressId?: number | null) =>
    http.post<{ message: string; code: string; totals: Totals }>('/coupons/apply', {
      code,
      payment_method: paymentMethod,
      shipping_method: shippingMethod,
      address_id: addressId || undefined,
    }),
};

export const OrderApi = {
  list: async () => (await http.get<{ data: Order[] }>('/orders')).data,
  show: async (orderNo: string) => (await http.get<{ data: Order }>(`/orders/${encodeURIComponent(orderNo)}`)).data,
  create: async (payload: {
    address_id: number;
    shipping_method: 'standard' | 'express';
    payment_method: 'upi' | 'card' | 'netbanking' | 'cod';
    coupon_code?: string;
    gateway_order_id?: string;
    gateway_payment_id?: string;
    gateway_signature?: string;
  }) => (await http.post<{ data: Order }>('/orders', payload)).data,
  reorder: (orderNo: string) => http.post<{ message: string }>(`/orders/${encodeURIComponent(orderNo)}/reorder`),
};

export const PaymentApi = {
  createGatewayOrder: (payload: { coupon_code?: string; address_id?: number | null; shipping_method?: string }) =>
    http.post<{ key_id: string; gateway_order_id: string; amount: number; currency: string }>(
      '/checkout/gateway-order',
      payload
    ),
};
