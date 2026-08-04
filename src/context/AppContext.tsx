import React, { createContext, useContext, useEffect, useMemo, useRef, useState, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Location from 'expo-location';
import { Address, Customer, Order, User } from './types';
import { ApiError, getToken, setToken } from '../api/client';
import {
  AddressApi,
  AuthApi,
  CartApi,
  CouponApi,
  HomeApi,
  CategoryApi,
  OrderApi,
  OtpChannel,
  PaymentApi,
  PushApi,
  WishlistApi,
} from '../api/endpoints';
import { CartResponse, Category, HomeData, Totals } from '../api/types';
import { initPush } from '../push/onesignal';

const ONBOARD_KEY = 'hc_onboarded_v1';
const DELIVERY_LABEL_KEY = 'hc_delivery_label_v1';

const EMPTY_CART: CartResponse = { items: [], totals: { sub: 0, disc: 0, gst: 0, deliv: 0, total: 0, count: 0 } };

type ShippingMethod = 'standard' | 'express';
type PaymentMethod = 'upi' | 'card' | 'netbanking' | 'cod';
type ToastState = { message: string; key: number } | null;

type AppContextValue = {
  ready: boolean;
  hasOnboarded: boolean;
  session: 'guest' | 'user' | null;
  otpChannel: OtpChannel;
  refreshOtpChannel: () => Promise<void>;
  user: User;
  home: HomeData | null;
  categories: Category[];
  cart: CartResponse;
  wishlist: Record<string, boolean>;
  addresses: Address[];
  selectedAddressId: number | null;
  deliveryLabel: string;
  deliveryLoading: boolean;
  useCurrentLocation: () => Promise<void>;
  setDeliveryAddress: (address: Address) => void;
  orders: Order[];
  ship: ShippingMethod;
  pay: PaymentMethod;
  toast: ToastState;
  couponCode: string;
  couponApplied: boolean;
  couponErr: string;

  completeOnboarding: () => void;
  requestOtp: (contact: string, channel: OtpChannel) => Promise<{ ok: boolean; debugOtp: string | null }>;
  verifyOtp: (contact: string, otp: string, channel: OtpChannel) => Promise<boolean>;
  enterAsGuest: () => Promise<void>;
  logout: () => Promise<void>;
  updateProfile: (payload: { name: string; business_name?: string; city?: string; state?: string }) => Promise<boolean>;

  addToCart: (id: string, q?: number, variantId?: string) => Promise<void>;
  incCart: (id: string, variantId?: string) => Promise<void>;
  decCart: (id: string, variantId?: string) => Promise<void>;
  removeCart: (id: string, variantId?: string) => Promise<void>;
  toggleWish: (id: string) => Promise<void>;

  setCouponCode: (v: string) => void;
  applyCoupon: () => Promise<void>;

  setSelectedAddressId: (id: number) => void;
  addAddress: (payload: {
    label?: string;
    name: string;
    line: string;
    city: string;
    state: string;
    pincode: string;
    is_default?: boolean;
  }) => Promise<Address>;
  setShip: (id: ShippingMethod) => void;
  setPay: (id: PaymentMethod) => void;

  beginOnlinePayment: () => Promise<{ key_id: string; gateway_order_id: string; amount: number; currency: string }>;
  placeOrder: (gatewayFields?: {
    gateway_order_id: string;
    gateway_payment_id: string;
    gateway_signature: string;
  }) => Promise<string>;
  reorder: (order: Order) => Promise<void>;

  totals: Totals;
  showToast: (msg: string) => void;
};

const AppContext = createContext<AppContextValue | null>(null);

const GUEST_USER: User = { name: 'Guest', email: '', gst: '', businessName: '', city: '', state: '', isGuest: true };

function toUser(customer: Customer | null): User {
  if (!customer) return GUEST_USER;
  return {
    name: customer.name || 'Guest',
    email: customer.email,
    gst: customer.gst || '',
    businessName: customer.business_name || '',
    city: customer.city || '',
    state: customer.state || '',
    isGuest: customer.is_guest,
  };
}

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [ready, setReady] = useState(false);
  const [hasOnboarded, setHasOnboarded] = useState(false);
  const [session, setSession] = useState<'guest' | 'user' | null>(null);
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [otpChannel, setOtpChannel] = useState<OtpChannel>('email');

  const [home, setHome] = useState<HomeData | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [cart, setCart] = useState<CartResponse>(EMPTY_CART);
  const [wishlist, setWishlist] = useState<Record<string, boolean>>({});
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [deliveryLabel, setDeliveryLabelState] = useState('');
  const [deliveryLoading, setDeliveryLoading] = useState(false);
  const [orders, setOrders] = useState<Order[]>([]);

  const [ship, setShip] = useState<ShippingMethod>('standard');
  const [pay, setPay] = useState<PaymentMethod>('upi');

  const [couponCode, setCouponCodeState] = useState('');
  const [couponApplied, setCouponApplied] = useState(false);
  const [couponErr, setCouponErr] = useState('');
  const [couponTotals, setCouponTotals] = useState<Totals | null>(null);

  const [toast, setToastState] = useState<ToastState>(null);
  const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const toastKey = useRef(0);
  const pushPlayerIdRef = useRef<string | null>(null);

  const registerPush = useCallback(async () => {
    const id = pushPlayerIdRef.current;
    if (!id) return;
    const token = await getToken();
    if (!token) return;
    try {
      await PushApi.register(id);
    } catch {
      // best-effort — push registration should never block or surface errors
    }
  }, []);

  useEffect(() => {
    initPush((id) => {
      pushPlayerIdRef.current = id;
      registerPush();
    });
  }, [registerPush]);

  const showToast = useCallback((msg: string) => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
    toastKey.current += 1;
    setToastState({ message: msg, key: toastKey.current });
    toastTimer.current = setTimeout(() => setToastState(null), 1900);
  }, []);

  const loadUserData = useCallback(async () => {
    const [homeRes, categoriesRes, cartRes, wishlistRes, addressesRes, ordersRes] = await Promise.allSettled([
      HomeApi.get(),
      CategoryApi.list(),
      CartApi.get(),
      WishlistApi.list(),
      AddressApi.list(),
      OrderApi.list(),
    ]);

    if (homeRes.status === 'fulfilled') setHome(homeRes.value);
    if (categoriesRes.status === 'fulfilled') setCategories(categoriesRes.value);
    if (cartRes.status === 'fulfilled') setCart(cartRes.value);
    if (wishlistRes.status === 'fulfilled') {
      const map: Record<string, boolean> = {};
      wishlistRes.value.forEach((p) => {
        map[p.id] = true;
      });
      setWishlist(map);
    }
    if (addressesRes.status === 'fulfilled') {
      setAddresses(addressesRes.value);
      const def = addressesRes.value.find((a) => a.is_default) || addressesRes.value[0];
      if (def) setSelectedAddressId(def.id);
    }
    if (ordersRes.status === 'fulfilled') setOrders(ordersRes.value);
  }, []);

  const refreshOtpChannel = useCallback(async () => {
    try {
      const res = await AuthApi.getOtpChannel();
      setOtpChannel(res.channel);
    } catch {
      // Leave whatever channel we last knew about — the Login screen still
      // needs to render something, and a stale-but-known value beats
      // silently defaulting to 'email'.
    }
  }, []);

  useEffect(() => {
    refreshOtpChannel();
  }, [refreshOtpChannel]);

  useEffect(() => {
    AsyncStorage.getItem(DELIVERY_LABEL_KEY).then((v) => {
      if (v) setDeliveryLabelState(v);
    });
  }, []);

  useEffect(() => {
    (async () => {
      try {
        const onboarded = await AsyncStorage.getItem(ONBOARD_KEY);
        setHasOnboarded(onboarded === '1');

        const token = await getToken();
        if (token) {
          try {
            const me = await AuthApi.me();
            setCustomer(me);
            setSession(me.is_guest ? 'guest' : 'user');
            await loadUserData();
          } catch (err) {
            if (err instanceof ApiError && (err.status === 401 || err.status === 419)) {
              await setToken(null);
            }
            setCustomer(null);
            setSession(null);
          }
        }
      } finally {
        setReady(true);
      }
    })();
  }, [loadUserData]);

  const completeOnboarding = useCallback(() => {
    setHasOnboarded(true);
    AsyncStorage.setItem(ONBOARD_KEY, '1').catch(() => {});
  }, []);

  const requestOtp = useCallback(async (contact: string, channel: OtpChannel): Promise<{ ok: boolean; debugOtp: string | null }> => {
    try {
      const res = await AuthApi.requestOtp(channel, contact);
      return { ok: true, debugOtp: res.debug_otp };
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not send verification code');
      return { ok: false, debugOtp: null };
    }
  }, [showToast]);

  const verifyOtp = useCallback(async (contact: string, otp: string, channel: OtpChannel): Promise<boolean> => {
    try {
      const res = await AuthApi.verifyOtp(channel, contact, otp);
      await setToken(res.token);
      setCustomer(res.customer);
      setSession(res.customer.is_guest ? 'guest' : 'user');
      setHasOnboarded(true);
      AsyncStorage.setItem(ONBOARD_KEY, '1').catch(() => {});
      await loadUserData();
      registerPush();
      return true;
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Verification failed');
      return false;
    }
  }, [loadUserData, showToast, registerPush]);

  const enterAsGuest = useCallback(async () => {
    try {
      const res = await AuthApi.guest();
      await setToken(res.token);
      setCustomer(res.customer);
      setSession('guest');
      setHasOnboarded(true);
      AsyncStorage.setItem(ONBOARD_KEY, '1').catch(() => {});
      await loadUserData();
      registerPush();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not start guest session');
    }
  }, [loadUserData, showToast, registerPush]);

  const logout = useCallback(async () => {
    try {
      await PushApi.unregister();
    } catch {
      // best-effort
    }
    try {
      await AuthApi.logout();
    } catch {
      // best-effort; proceed to clear local session regardless
    }
    await setToken(null);
    setCustomer(null);
    setSession(null);
    setHome(null);
    setCategories([]);
    setCart(EMPTY_CART);
    setWishlist({});
    setAddresses([]);
    setSelectedAddressId(null);
    setOrders([]);
    setCouponApplied(false);
    setCouponCodeState('');
    setCouponTotals(null);
  }, []);

  const resetCoupon = useCallback(() => {
    setCouponApplied(false);
    setCouponTotals(null);
    setCouponErr('');
  }, []);

  const addToCart = useCallback(async (id: string, q: number = 1, variantId?: string) => {
    try {
      const res = await CartApi.add(id, q, variantId);
      setCart(res);
      resetCoupon();
      showToast('Added to cart');
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not add to cart');
    }
  }, [resetCoupon, showToast]);

  const incCart = useCallback(async (id: string, variantId?: string) => {
    const current = cart.items.find((i) => i.product.id === id && (i.variant?.id ?? undefined) === variantId);
    const nextQty = (current?.quantity || 0) + 1;
    try {
      const res = await CartApi.update(id, nextQty, variantId);
      setCart(res);
      resetCoupon();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not update cart');
    }
  }, [cart.items, resetCoupon, showToast]);

  const decCart = useCallback(async (id: string, variantId?: string) => {
    const current = cart.items.find((i) => i.product.id === id && (i.variant?.id ?? undefined) === variantId);
    const nextQty = (current?.quantity || 0) - 1;
    try {
      const res = nextQty <= 0 ? await CartApi.remove(id, variantId) : await CartApi.update(id, nextQty, variantId);
      setCart(res);
      resetCoupon();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not update cart');
    }
  }, [cart.items, resetCoupon, showToast]);

  const removeCart = useCallback(async (id: string, variantId?: string) => {
    try {
      const res = await CartApi.remove(id, variantId);
      setCart(res);
      resetCoupon();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not remove item');
    }
  }, [resetCoupon, showToast]);

  const toggleWish = useCallback(async (id: string) => {
    const wasWished = !!wishlist[id];
    setWishlist((w) => {
      const next = { ...w };
      if (wasWished) delete next[id];
      else next[id] = true;
      return next;
    });
    try {
      if (wasWished) await WishlistApi.remove(id);
      else await WishlistApi.add(id);
    } catch (err) {
      setWishlist((w) => {
        const next = { ...w };
        if (wasWished) next[id] = true;
        else delete next[id];
        return next;
      });
      showToast(err instanceof ApiError ? err.message : 'Could not update wishlist');
    }
  }, [wishlist, showToast]);

  const setCouponCode = useCallback((v: string) => {
    setCouponCodeState(v);
    setCouponErr('');
  }, []);

  const applyCoupon = useCallback(async () => {
    const code = (couponCode || '').trim().toUpperCase();
    if (!code) {
      setCouponErr('Enter a coupon code');
      return;
    }
    try {
      const res = await CouponApi.apply(code);
      setCouponApplied(true);
      setCouponTotals(res.totals);
      setCouponErr('');
      showToast('Coupon applied');
    } catch (err) {
      setCouponApplied(false);
      setCouponTotals(null);
      setCouponErr(err instanceof ApiError ? err.message : 'Invalid coupon code');
    }
  }, [couponCode, showToast]);

  const addAddress = useCallback(async (payload: {
    label?: string;
    name: string;
    line: string;
    city: string;
    state: string;
    pincode: string;
    is_default?: boolean;
  }): Promise<Address> => {
    const address = await AddressApi.create(payload);
    setAddresses((prev) => [...prev, address]);
    setSelectedAddressId(address.id);
    return address;
  }, []);

  const setDeliveryLabel = useCallback((label: string) => {
    setDeliveryLabelState(label);
    AsyncStorage.setItem(DELIVERY_LABEL_KEY, label).catch(() => {});
  }, []);

  const useCurrentLocation = useCallback(async () => {
    setDeliveryLoading(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        showToast('Location permission denied');
        return;
      }
      const pos = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
      const places = await Location.reverseGeocodeAsync({
        latitude: pos.coords.latitude,
        longitude: pos.coords.longitude,
      });
      const place = places[0];
      const locality = place?.city || place?.subregion || place?.district;
      const label = [locality, place?.postalCode].filter(Boolean).join(' ');
      if (!label) {
        showToast('Could not determine your address');
        return;
      }
      setDeliveryLabel(label);
      showToast('Delivery location updated');
    } catch {
      showToast('Could not get your location. Check location permissions.');
    } finally {
      setDeliveryLoading(false);
    }
  }, [showToast, setDeliveryLabel]);

  const setDeliveryAddress = useCallback((address: Address) => {
    setDeliveryLabel(`${address.city} ${address.pincode}`);
    setSelectedAddressId(address.id);
  }, [setDeliveryLabel]);

  const totals: Totals = useMemo(() => (couponApplied && couponTotals ? couponTotals : cart.totals), [couponApplied, couponTotals, cart.totals]);

  const beginOnlinePayment = useCallback(async () => {
    return PaymentApi.createGatewayOrder({
      coupon_code: couponApplied ? couponCode.trim().toUpperCase() : undefined,
    });
  }, [couponApplied, couponCode]);

  const placeOrder = useCallback(async (gatewayFields?: {
    gateway_order_id: string;
    gateway_payment_id: string;
    gateway_signature: string;
  }): Promise<string> => {
    if (!selectedAddressId) throw new Error('Select a delivery address first');
    const order = await OrderApi.create({
      address_id: selectedAddressId,
      shipping_method: ship,
      payment_method: pay,
      coupon_code: couponApplied ? couponCode.trim().toUpperCase() : undefined,
      ...gatewayFields,
    });
    setOrders((prev) => [order, ...prev]);
    setCart(EMPTY_CART);
    resetCoupon();
    setCouponCodeState('');
    return order.no;
  }, [selectedAddressId, ship, pay, couponApplied, couponCode, resetCoupon]);

  const updateProfile = useCallback(async (payload: { name: string; business_name?: string; city?: string; state?: string }): Promise<boolean> => {
    try {
      const updated = await AuthApi.updateMe(payload);
      setCustomer(updated);
      showToast('Profile updated');
      return true;
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not update profile');
      return false;
    }
  }, [showToast]);

  const reorder = useCallback(async (order: Order) => {
    try {
      await OrderApi.reorder(order.no);
      const res = await CartApi.get();
      setCart(res);
      showToast('Items added to cart');
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not reorder');
    }
  }, [showToast]);

  const value: AppContextValue = {
    ready,
    hasOnboarded,
    session,
    otpChannel,
    refreshOtpChannel,
    user: toUser(customer),
    home,
    categories,
    cart,
    wishlist,
    addresses,
    selectedAddressId,
    deliveryLabel,
    deliveryLoading,
    orders,
    ship,
    pay,
    toast,
    couponCode,
    couponApplied,
    couponErr,
    completeOnboarding,
    requestOtp,
    verifyOtp,
    enterAsGuest,
    logout,
    updateProfile,
    addToCart,
    incCart,
    decCart,
    removeCart,
    toggleWish,
    setCouponCode,
    applyCoupon,
    setSelectedAddressId,
    addAddress,
    useCurrentLocation,
    setDeliveryAddress,
    setShip,
    setPay,
    beginOnlinePayment,
    placeOrder,
    reorder,
    totals,
    showToast,
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp(): AppContextValue {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error('useApp must be used within AppProvider');
  return ctx;
}
