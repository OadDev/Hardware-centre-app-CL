export type { Order, OrderStatus, OrderItem, Address, Customer } from '../api/types';

export type User = {
  name: string;
  email: string;
  gst: string;
  businessName: string;
  city: string;
  state: string;
  isGuest: boolean;
};
