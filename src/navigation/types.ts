import { NavigatorScreenParams } from '@react-navigation/native';

export type MainTabParamList = {
  Home: undefined;
  Categories: undefined;
  Cart: undefined;
  Orders: undefined;
  Profile: undefined;
};

export type RootStackParamList = {
  Splash: undefined;
  Onboarding: undefined;
  Welcome: undefined;
  Login: undefined;
  Otp: { email: string; debugOtp?: string | null };
  Main: NavigatorScreenParams<MainTabParamList>;
  Search: undefined;
  CategoryListing: { catId: string };
  ProductDetail: { id: string };
  Checkout: undefined;
  OrderSuccess: { orderNo: string };
};

declare global {
  namespace ReactNavigation {
    interface RootParamList extends RootStackParamList {}
  }
}
