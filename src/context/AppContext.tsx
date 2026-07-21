import React, { createContext, useContext, useEffect, useMemo, useRef, useState, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
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
  WishlistApi,
} from '../api/endpoints';
import { CartResponse, Category, HomeData, Totals } from '../api/types';

const ONBOARD_KEY = 'hc_onboarded_v1';

const EMPTY_CART: CartResponse = { items: [], totals: { sub: 0, disc: 0, gst: 0, deliv: 0, total: 0, count: 0 } };

type ShippingMethod = 'standard' | 'express';
type PaymentMethod = 'upi' | 'card' | 'netbanking' | 'cod';
type ToastState = { message: string; key: number } | null;

type AppContextValue = {
  ready: boolean;
  hasOnboarded: boolean;
  session: 'guest' | 'user' | null;
  user: User;
  home: HomeData | null;
  categories: Category[];
  cart: CartResponse;
  wishlist: Record<string, boolean>;
  addresses: Address[];
  selectedAddressId: number | null;
  orders: Order[];
  ship: ShippingMethod;
  pay: PaymentMethod;
  toast: ToastState;
  couponCode: string;
  couponApplied: boolean;
  couponErr: string;

  completeOnboarding: () => void;
  requestOtp: (email: string) => Promise<string | null>;
  verifyOtp: (email: string, otp: string) => Promise<boolean>;
  enterAsGuest: () => Promise<void>;
  logout: () => Promise<void>;

  addToCart: (id: string, q?: number) => Promise<void>;
  incCart: (id: string) => Promise<void>;
  decCart: (id: string) => Promise<void>;
  removeCart: (id: string) => Promise<void>;
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

  placeOrder: () => Promise<string>;
  reorder: (order: Order) => Promise<void>;

  totals: Totals;
  showToast: (msg: string) => void;
};

const AppContext = createContext<AppContextValue | null>(null);

const GUEST_USER: User = { name: 'Guest', email: '', gst: '', isGuest: true };

function toUser(customer: Customer | null): User {
  if (!customer) return GUEST_USER;
  return {
    name: customer.name || 'Guest',
    email: customer.email,
    gst: customer.gst || '',
    isGuest: customer.is_guest,
  };
}

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [ready, setReady] = useState(false);
  const [hasOnboarded, setHasOnboarded] = useState(false);
  const [session, setSession] = useState<'guest' | 'user' | null>(null);
  const [customer, setCustomer] = useState<Customer | null>(null);

  const [home, setHome] = useState<HomeData | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [cart, setCart] = useState<CartResponse>(EMPTY_CART);
  const [wishlist, setWishlist] = useState<Record<string, boolean>>({});
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
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

  const requestOtp = useCallback(async (email: string): Promise<string | null> => {
    try {
      const res = await AuthApi.requestOtp(email);
      return res.debug_otp;
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not send verification code');
      return null;
    }
  }, [showToast]);

  const verifyOtp = useCallback(async (email: string, otp: string): Promise<boolean> => {
    try {
      const res = await AuthApi.verifyOtp(email, otp);
      await setToken(res.token);
      setCustomer(res.customer);
      setSession(res.customer.is_guest ? 'guest' : 'user');
      setHasOnboarded(true);
      AsyncStorage.setItem(ONBOARD_KEY, '1').catch(() => {});
      await loadUserData();
      return true;
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Verification failed');
      return false;
    }
  }, [loadUserData, showToast]);

  const enterAsGuest = useCallback(async () => {
    try {
      const res = await AuthApi.guest();
      await setToken(res.token);
      setCustomer(res.customer);
      setSession('guest');
      setHasOnboarded(true);
      AsyncStorage.setItem(ONBOARD_KEY, '1').catch(() => {});
      await loadUserData();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not start guest session');
    }
  }, [loadUserData, showToast]);

  const logout = useCallback(async () => {
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

  const addToCart = useCallback(async (id: string, q: number = 1) => {
    try {
      const res = await CartApi.add(id, q);
      setCart(res);
      resetCoupon();
      showToast('Added to cart');
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not add to cart');
    }
  }, [resetCoupon, showToast]);

  const incCart = useCallback(async (id: string) => {
    const current = cart.items.find((i) => i.product.id === id);
    const nextQty = (current?.quantity || 0) + 1;
    try {
      const res = await CartApi.update(id, nextQty);
      setCart(res);
      resetCoupon();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not update cart');
    }
  }, [cart.items, resetCoupon, showToast]);

  const decCart = useCallback(async (id: string) => {
    const current = cart.items.find((i) => i.product.id === id);
    const nextQty = (current?.quantity || 0) - 1;
    try {
      const res = nextQty <= 0 ? await CartApi.remove(id) : await CartApi.update(id, nextQty);
      setCart(res);
      resetCoupon();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Could not update cart');
    }
  }, [cart.items, resetCoupon, showToast]);

  const removeCart = useCallback(async (id: string) => {
    try {
      const res = await CartApi.remove(id);
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

  const totals: Totals = useMemo(() => (couponApplied && couponTotals ? couponTotals : cart.totals), [couponApplied, couponTotals, cart.totals]);

  const placeOrder = useCallback(async (): Promise<string> => {
    if (!selectedAddressId) throw new Error('Select a delivery address first');
    const order = await OrderApi.create({
      address_id: selectedAddressId,
      shipping_method: ship,
      payment_method: pay,
      coupon_code: couponApplied ? couponCode.trim().toUpperCase() : undefined,
    });
    setOrders((prev) => [order, ...prev]);
    setCart(EMPTY_CART);
    resetCoupon();
    setCouponCodeState('');
    return order.no;
  }, [selectedAddressId, ship, pay, couponApplied, couponCode, resetCoupon]);

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
    user: toUser(customer),
    home,
    categories,
    cart,
    wishlist,
    addresses,
    selectedAddressId,
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
    addToCart,
    incCart,
    decCart,
    removeCart,
    toggleWish,
    setCouponCode,
    applyCoupon,
    setSelectedAddressId,
    addAddress,
    setShip,
    setPay,
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
