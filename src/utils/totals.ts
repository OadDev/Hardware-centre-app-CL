import { findProduct } from '../data/catalog';

export type CartMap = Record<string, number>;

export type Totals = {
  sub: number;
  disc: number;
  gst: number;
  deliv: number;
  total: number;
  count: number;
};

export function computeTotals(cart: CartMap, couponApplied: boolean, couponVal: number): Totals {
  const items = Object.entries(cart);
  let sub = 0;
  items.forEach(([id, q]) => {
    sub += findProduct(id).price * q;
  });
  const disc = couponApplied ? Math.min(couponVal, sub) : 0;
  const taxable = sub - disc;
  const gst = taxable * 0.18;
  const deliv = sub >= 5000 || sub === 0 ? 0 : 99;
  const total = taxable + gst + deliv;
  const count = items.reduce((a, [, q]) => a + q, 0);
  return { sub, disc, gst, deliv, total, count };
}
