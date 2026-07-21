import React, { createContext, useContext, useEffect, useMemo, useRef, useState, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Address, Order, User } from './types';
import { COUPONS, findProduct } from '../data/catalog';
import { computeTotals, CartMap } from '../utils/totals';

const STORAGE_KEY = 'hc_app_state_v1';

const SEED_ORDERS: Order[] = [
  { no: 'HC-2026-00519', status: 'ongoing', stage: 'Out for delivery', date: 'Placed 24 Jun 2026', sub: 'Arrives by 27 Jun', items: [{ id: 'p1', q: 1 }, { id: 'p9', q: 2 }], total: 4520 },
  { no: 'HC-2026-00481', status: 'delivered', stage: 'Delivered', date: 'Delivered 12 Jun 2026', sub: 'Rate your order', items: [{ id: 'p6', q: 2 }, { id: 'p4', q: 3 }], total: 2898 },
  { no: 'HC-2026-00342', status: 'cancelled', stage: 'Cancelled by you', date: '24 May 2026', sub: 'Refund of ₹1,180 completed', items: [{ id: 'p2', q: 1 }], total: 1180 },
  { no: 'HC-2026-00298', status: 'returned', stage: 'Return completed', date: '10 May 2026', sub: 'Refund of ₹1,850 processed', items: [{ id: 'p8', q: 1 }], total: 1850 },
];

const DEFAULT_USER: User = {
  name: 'Kumar Hardware',
  email: 'rajesh@kumarhardware.in',
  gst: '07AABCK1234M1Z5',
  isGuest: false,
};

export const ADDRESSES: Address[] = [
  { id: 0, label: 'Shop', name: 'Kumar Hardware & Tools', line: 'Shop 14, Bhagirath Palace, Chandni Chowk', city: 'New Delhi, Delhi 110006' },
  { id: 1, label: 'Warehouse', name: 'Kumar Hardware (Godown)', line: 'Plot 22, Sector 18, Udyog Vihar', city: 'Gurugram, Haryana 122015' },
];

type PersistedState = {
  hasOnboarded: boolean;
  session: 'guest' | 'user' | null;
  user: User;
  cart: CartMap;
  wishlist: Record<string, boolean>;
  orders: Order[];
  addrId: number;
  ship: 'standard' | 'express';
  pay: 'upi' | 'card' | 'netbanking' | 'cod';
};

const DEFAULT_STATE: PersistedState = {
  hasOnboarded: false,
  session: null,
  user: DEFAULT_USER,
  cart: {},
  wishlist: { p6: true, p1: true },
  orders: SEED_ORDERS,
  addrId: 0,
  ship: 'standard',
  pay: 'upi',
};

type ToastState = { message: string; key: number } | null;

type AppContextValue = {
  ready: boolean;
  hasOnboarded: boolean;
  session: 'guest' | 'user' | null;
  user: User;
  cart: CartMap;
  wishlist: Record<string, boolean>;
  orders: Order[];
  addrId: number;
  ship: 'standard' | 'express';
  pay: 'upi' | 'card' | 'netbanking' | 'cod';
  toast: ToastState;
  couponCode: string;
  couponApplied: boolean;
  couponVal: number;
  couponErr: string;

  completeOnboarding: () => void;
  loginAs: (email: string) => void;
  enterAsGuest: () => void;
  logout: () => void;

  addToCart: (id: string, q?: number) => void;
  incCart: (id: string) => void;
  decCart: (id: string) => void;
  removeCart: (id: string) => void;
  clearCart: () => void;
  toggleWish: (id: string) => void;

  setCouponCode: (v: string) => void;
  applyCoupon: () => void;

  setAddrId: (id: number) => void;
  setShip: (id: 'standard' | 'express') => void;
  setPay: (id: 'upi' | 'card' | 'netbanking' | 'cod') => void;

  placeOrder: () => string;
  reorder: (order: Order) => void;

  totals: ReturnType<typeof computeTotals>;
  showToast: (msg: string) => void;
};

const AppContext = createContext<AppContextValue | null>(null);

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [ready, setReady] = useState(false);
  const [state, setState] = useState<PersistedState>(DEFAULT_STATE);
  const [couponCode, setCouponCodeState] = useState('');
  const [couponApplied, setCouponApplied] = useState(false);
  const [couponVal, setCouponVal] = useState(0);
  const [couponErr, setCouponErr] = useState('');
  const [toast, setToast] = useState<ToastState>(null);
  const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const toastKey = useRef(0);

  useEffect(() => {
    (async () => {
      try {
        const raw = await AsyncStorage.getItem(STORAGE_KEY);
        if (raw) {
          const parsed = JSON.parse(raw);
          setState((s) => ({ ...s, ...parsed }));
        }
      } catch {
        // ignore corrupt storage
      } finally {
        setReady(true);
      }
    })();
  }, []);

  useEffect(() => {
    if (!ready) return;
    AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(state)).catch(() => {});
  }, [state, ready]);

  const showToast = useCallback((msg: string) => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
    toastKey.current += 1;
    setToast({ message: msg, key: toastKey.current });
    toastTimer.current = setTimeout(() => setToast(null), 1900);
  }, []);

  const completeOnboarding = useCallback(() => {
    setState((s) => ({ ...s, hasOnboarded: true }));
  }, []);

  const loginAs = useCallback((email: string) => {
    setState((s) => ({ ...s, session: 'user', user: { ...s.user, email, isGuest: false }, hasOnboarded: true }));
  }, []);

  const enterAsGuest = useCallback(() => {
    setState((s) => ({ ...s, session: 'guest', user: { ...s.user, isGuest: true }, hasOnboarded: true }));
  }, []);

  const logout = useCallback(() => {
    setState((s) => ({ ...s, session: null, cart: {} }));
  }, []);

  const addToCart = useCallback((id: string, q: number = 1) => {
    setState((s) => ({ ...s, cart: { ...s.cart, [id]: (s.cart[id] || 0) + q } }));
    showToast('Added to cart');
  }, [showToast]);

  const incCart = useCallback((id: string) => {
    setState((s) => ({ ...s, cart: { ...s.cart, [id]: (s.cart[id] || 0) + 1 } }));
  }, []);

  const decCart = useCallback((id: string) => {
    setState((s) => {
      const q = (s.cart[id] || 0) - 1;
      const c = { ...s.cart };
      if (q <= 0) delete c[id];
      else c[id] = q;
      return { ...s, cart: c };
    });
  }, []);

  const removeCart = useCallback((id: string) => {
    setState((s) => {
      const c = { ...s.cart };
      delete c[id];
      return { ...s, cart: c };
    });
  }, []);

  const clearCart = useCallback(() => {
    setState((s) => ({ ...s, cart: {} }));
  }, []);

  const toggleWish = useCallback((id: string) => {
    setState((s) => {
      const w = { ...s.wishlist };
      if (w[id]) delete w[id];
      else w[id] = true;
      return { ...s, wishlist: w };
    });
  }, []);

  const setCouponCode = useCallback((v: string) => {
    setCouponCodeState(v);
    setCouponErr('');
  }, []);

  const applyCoupon = useCallback(() => {
    const code = (couponCode || '').trim().toUpperCase();
    const val = COUPONS[code];
    if (val) {
      setCouponApplied(true);
      setCouponVal(val);
      setCouponErr('');
      showToast('Coupon applied');
    } else {
      setCouponApplied(false);
      setCouponVal(0);
      setCouponErr('Invalid coupon code');
    }
  }, [couponCode, showToast]);

  const setAddrId = useCallback((id: number) => setState((s) => ({ ...s, addrId: id })), []);
  const setShip = useCallback((id: 'standard' | 'express') => setState((s) => ({ ...s, ship: id })), []);
  const setPay = useCallback((id: 'upi' | 'card' | 'netbanking' | 'cod') => setState((s) => ({ ...s, pay: id })), []);

  const totals = useMemo(() => computeTotals(state.cart, couponApplied, couponVal), [state.cart, couponApplied, couponVal]);

  const placeOrder = useCallback((): string => {
    const no = 'HC-2026-00' + (520 + Math.floor(Math.random() * 80));
    const items = Object.entries(state.cart).map(([id, q]) => ({ id, q }));
    const t = computeTotals(state.cart, couponApplied, couponVal);
    const newOrder: Order = {
      no,
      status: 'ongoing',
      stage: 'Order confirmed',
      date: 'Placed 26 Jun 2026',
      sub: 'Arrives by 29 Jun',
      items,
      total: t.total,
    };
    setState((s) => ({ ...s, orders: [newOrder, ...s.orders], cart: {} }));
    setCouponApplied(false);
    setCouponVal(0);
    setCouponCodeState('');
    return no;
  }, [state.cart, couponApplied, couponVal]);

  const reorder = useCallback((order: Order) => {
    setState((s) => {
      const c = { ...s.cart };
      order.items.forEach((it) => {
        c[it.id] = (c[it.id] || 0) + it.q;
      });
      return { ...s, cart: c };
    });
  }, []);

  const value: AppContextValue = {
    ready,
    hasOnboarded: state.hasOnboarded,
    session: state.session,
    user: state.user,
    cart: state.cart,
    wishlist: state.wishlist,
    orders: state.orders,
    addrId: state.addrId,
    ship: state.ship,
    pay: state.pay,
    toast,
    couponCode,
    couponApplied,
    couponVal,
    couponErr,
    completeOnboarding,
    loginAs,
    enterAsGuest,
    logout,
    addToCart,
    incCart,
    decCart,
    removeCart,
    clearCart,
    toggleWish,
    setCouponCode,
    applyCoupon,
    setAddrId,
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

export function decoProduct(id: string, wishlist: Record<string, boolean>) {
  const p = findProduct(id);
  return { ...p, wished: !!wishlist[id] };
}
