export type OrderStatus = 'ongoing' | 'delivered' | 'cancelled' | 'returned';

export type OrderItem = { id: string; q: number };

export type Order = {
  no: string;
  status: OrderStatus;
  stage: string;
  date: string;
  sub: string;
  items: OrderItem[];
  total: number;
};

export type User = {
  name: string;
  email: string;
  gst: string;
  isGuest: boolean;
};

export type Address = {
  id: number;
  label: string;
  name: string;
  line: string;
  city: string;
};
