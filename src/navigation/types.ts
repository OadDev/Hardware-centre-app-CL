import { NavigatorScreenParams } from '@react-navigation/native';

export type MainTabParamList = {
  Home: undefined;
  Categories: undefined;
  Cart: undefined;
  Orders: undefined;
  Profile: undefined;
};

export type ProductListParams =
  | { title: string; source: 'popular' | 'trending' | 'recommended' | 'deals' }
  | { title: string; supplier: string }
  | { title: string; brand: string };

export type RootStackParamList = {
  Splash: undefined;
  Onboarding: undefined;
  Welcome: undefined;
  Login: { mode?: 'login' | 'signup' } | undefined;
  Otp: { contact: string; channel: 'email' | 'sms'; debugOtp?: string | null };
  Main: NavigatorScreenParams<MainTabParamList>;
  Search: undefined;
  CategoryListing: { catId: string };
  ProductDetail: { id: string };
  ProductList: ProductListParams;
  Suppliers: undefined;
  Wishlist: undefined;
  Addresses: undefined;
  OrderDetail: { orderNo: string };
  Checkout: undefined;
  RazorpayCheckout: { key_id: string; gateway_order_id: string; amount: number; currency: string };
  OrderSuccess: { orderNo: string };
};

declare global {
  namespace ReactNavigation {
    interface RootParamList extends RootStackParamList {}
  }
}
