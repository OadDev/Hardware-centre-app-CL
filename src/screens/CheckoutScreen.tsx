import React, { useState } from 'react';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { inr } from '../utils/format';
import { BackButton } from '../components/UI';
import { IconPlus } from '../components/Icons';
import AddressFormModal from '../components/AddressFormModal';

type Props = NativeStackScreenProps<RootStackParamList, 'Checkout'>;

const SHIPPING = [
  { id: 'standard' as const, title: 'Standard Delivery', sub: '3–5 business days', price: 'Free' },
  { id: 'express' as const, title: 'Express Delivery', sub: '1–2 business days', price: '₹149' },
];

const PAYMENTS = [
  { id: 'upi' as const, title: 'UPI', sub: 'GPay, PhonePe, Paytm' },
  { id: 'card' as const, title: 'Credit / Debit Card', sub: 'Visa, Mastercard, RuPay' },
  { id: 'netbanking' as const, title: 'Net Banking', sub: 'All major banks' },
  { id: 'cod' as const, title: 'Pay on Delivery', sub: 'Cash / UPI on arrival' },
];

function RadioDot({ active }: { active: boolean }) {
  return (
    <View style={[styles.radio, active ? styles.radioActive : null]}>
      {active && <View style={styles.radioDot} />}
    </View>
  );
}

export default function CheckoutScreen({ navigation }: Props) {
  const { addresses, selectedAddressId, setSelectedAddressId, ship, setShip, pay, setPay, totals, beginOnlinePayment, placeOrder, showToast } = useApp();
  const [placing, setPlacing] = useState(false);
  const [showAddForm, setShowAddForm] = useState(false);

  const onPlaceOrder = async () => {
    if (!selectedAddressId) {
      Alert.alert(
        'Save your address first',
        'You need a delivery address before you can place this order.',
        [
          { text: 'Cancel', style: 'cancel' },
          { text: 'Add Address', onPress: () => setShowAddForm(true) },
        ]
      );
      return;
    }
    setPlacing(true);
    try {
      if (pay === 'cod') {
        const orderNo = await placeOrder();
        navigation.replace('OrderSuccess', { orderNo });
      } else {
        const gatewayOrder = await beginOnlinePayment();
        navigation.navigate('RazorpayCheckout', gatewayOrder);
      }
    } catch (err) {
      showToast(err instanceof Error ? err.message : 'Could not place order');
    } finally {
      setPlacing(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>Checkout</Text>
      </View>

      <ScrollView contentContainerStyle={{ paddingBottom: 24 }}>
        <View style={styles.section}>
          <Text style={styles.h2}>Delivery Address</Text>
          <View style={{ gap: 11 }}>
            {addresses.map((a) => {
              const active = a.id === selectedAddressId;
              return (
                <Pressable
                  key={a.id}
                  onPress={() => setSelectedAddressId(a.id)}
                  style={[styles.addressCard, { borderColor: active ? colors.accent : colors.borderStrong, backgroundColor: active ? colors.accentLight : '#fff' }]}
                >
                  <RadioDot active={active} />
                  <View style={{ flex: 1 }}>
                    <View style={styles.addressTopRow}>
                      <Text style={styles.addressName}>{a.name}</Text>
                      <View style={styles.addressLabel}>
                        <Text style={styles.addressLabelText}>{a.label}</Text>
                      </View>
                    </View>
                    <Text style={styles.addressLine}>{a.line}, {a.city}, {a.state} {a.pincode}</Text>
                  </View>
                </Pressable>
              );
            })}
            <Pressable style={styles.addAddressBtn} onPress={() => setShowAddForm(true)}>
              <IconPlus size={17} color={colors.textSub} strokeWidth={2.2} />
              <Text style={styles.addAddressText}>Add New Address</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.h2}>Shipping Method</Text>
          <View style={{ gap: 11 }}>
            {SHIPPING.map((x) => {
              const active = x.id === ship;
              return (
                <Pressable key={x.id} onPress={() => setShip(x.id)} style={[styles.shipCard, { borderColor: active ? colors.accent : colors.borderStrong }]}>
                  <RadioDot active={active} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.shipTitle}>{x.title}</Text>
                    <Text style={styles.shipSub}>{x.sub}</Text>
                  </View>
                  <Text style={styles.shipPrice}>{x.price}</Text>
                </Pressable>
              );
            })}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.h2}>Payment Method</Text>
          <View style={styles.payBox}>
            {PAYMENTS.map((x, i) => {
              const active = x.id === pay;
              return (
                <Pressable
                  key={x.id}
                  onPress={() => setPay(x.id)}
                  style={[styles.payRow, i === PAYMENTS.length - 1 ? { borderBottomWidth: 0 } : null]}
                >
                  <RadioDot active={active} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.shipTitle}>{x.title}</Text>
                    <Text style={styles.shipSub}>{x.sub}</Text>
                  </View>
                </Pressable>
              );
            })}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.h2}>Order Summary</Text>
          <View style={styles.summaryBox}>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Subtotal</Text>
              <Text style={styles.summaryVal}>{inr(totals.sub)}</Text>
            </View>
            {totals.disc > 0 && (
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Discount</Text>
                <Text style={[styles.summaryVal, { color: colors.successDark }]}>-{inr(totals.disc)}</Text>
              </View>
            )}
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>GST</Text>
              <Text style={styles.summaryVal}>{inr(totals.gst)}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Delivery</Text>
              <Text style={[styles.summaryVal, { color: totals.deliv === 0 ? colors.successDark : colors.text }]}>
                {totals.deliv === 0 ? 'Free' : inr(totals.deliv)}
              </Text>
            </View>
            {totals.packaging > 0 && (
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Packaging</Text>
                <Text style={styles.summaryVal}>{inr(totals.packaging)}</Text>
              </View>
            )}
            {totals.codFee > 0 && (
              <View style={[styles.summaryRow, { marginBottom: 12 }]}>
                <Text style={styles.summaryLabel}>COD Charges</Text>
                <Text style={styles.summaryVal}>{inr(totals.codFee)}</Text>
              </View>
            )}
            <View style={styles.summaryTotalRow}>
              <Text style={styles.summaryTotalLabel}>Total Payable</Text>
              <Text style={styles.summaryTotalVal}>{inr(totals.total)}</Text>
            </View>
          </View>
        </View>
      </ScrollView>

      <SafeAreaView edges={['bottom']} style={styles.stickyBar}>
        <View>
          <Text style={styles.stickyLabel}>Payable</Text>
          <Text style={styles.stickyVal}>{inr(totals.total)}</Text>
        </View>
        <Pressable style={[styles.placeOrderBtn, placing ? { opacity: 0.7 } : null]} onPress={onPlaceOrder} disabled={placing}>
          <Text style={styles.placeOrderText}>{placing ? 'Placing…' : 'Place Order'}</Text>
        </Pressable>
      </SafeAreaView>

      <AddressFormModal visible={showAddForm} onClose={() => setShowAddForm(false)} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bgAlt },
  topBar: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    paddingHorizontal: 14,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  topTitle: { fontSize: 18, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  section: { paddingHorizontal: 20, paddingTop: 24 },
  h2: { fontSize: 15, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 12 },
  addressCard: { borderWidth: 1.5, borderRadius: radii.lg, padding: 14, flexDirection: 'row', gap: 12 },
  addressTopRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  addressName: { fontSize: 14, fontFamily: fonts.bodyBold, color: colors.text },
  addressLabel: { backgroundColor: colors.accentLight, paddingHorizontal: 7, paddingVertical: 2, borderRadius: 6 },
  addressLabelText: { fontSize: 10, fontFamily: fonts.bodyBold, color: colors.accentDark },
  addressLine: { fontSize: 12.5, color: colors.textMuted, marginTop: 5, lineHeight: 18 },
  addAddressBtn: { borderWidth: 1.5, borderStyle: 'dashed', borderColor: colors.borderStrong, borderRadius: radii.lg, padding: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
  addAddressText: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.textSub },
  shipCard: { borderWidth: 1.5, borderRadius: radii.lg, padding: 14, flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: '#fff' },
  shipTitle: { fontSize: 14, fontFamily: fonts.bodyBold, color: colors.text },
  shipSub: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
  shipPrice: { fontSize: 14, fontFamily: fonts.bodyBold, color: colors.successDark },
  payBox: { backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.lg, overflow: 'hidden' },
  payRow: { padding: 14, borderBottomWidth: 1, borderBottomColor: colors.divider, flexDirection: 'row', alignItems: 'center', gap: 12 },
  radio: { width: 20, height: 20, borderRadius: 10, borderWidth: 1.5, borderColor: colors.borderStrong, alignItems: 'center', justifyContent: 'center' },
  radioActive: { borderColor: colors.accent },
  radioDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: colors.accent },
  summaryBox: { backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.lg, padding: 16 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10 },
  summaryLabel: { fontSize: 13, color: colors.textMuted },
  summaryVal: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.text },
  summaryTotalRow: { borderTopWidth: 1, borderTopColor: colors.borderStrong, borderStyle: 'dashed', paddingTop: 12, flexDirection: 'row', justifyContent: 'space-between' },
  summaryTotalLabel: { fontSize: 14.5, fontFamily: fonts.displayExtra, color: colors.text },
  summaryTotalVal: { fontSize: 17, fontFamily: fonts.displayExtra, color: colors.text },
  stickyBar: {
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    ...shadow.sticky,
  },
  stickyLabel: { fontSize: 11, color: colors.textMuted },
  stickyVal: { fontSize: 19, fontFamily: fonts.displayExtra, color: colors.text },
  placeOrderBtn: { flex: 1, height: 52, borderRadius: radii.md, backgroundColor: colors.accent, alignItems: 'center', justifyContent: 'center' },
  placeOrderText: { fontSize: 15.5, fontFamily: fonts.bodyBold, color: '#fff' },
});
