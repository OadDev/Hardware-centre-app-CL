export function inr(n: number): string {
  return '₹' + Math.round(n).toLocaleString('en-IN');
}

export function discountPct(price: number, mrp: number): number {
  if (!mrp) return 0;
  return Math.round((1 - price / mrp) * 100);
}

export function initials(name: string): string {
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}
