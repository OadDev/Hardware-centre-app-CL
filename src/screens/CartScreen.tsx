import React from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { findProduct } from '../data/catalog';
import { inr } from '../utils/format';
import PlaceholderImage from '../components/PlaceholderImage';
import { PrimaryButton } from '../components/UI';
import { IconCart, IconChevronRight, IconMinus, IconPlus, IconTicket, IconTrash } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function CartScreen() {
  const navigation = useNavigation<Nav>();
  const { cart, incCart, decCart, removeCart, totals, couponCode, setCouponCode, applyCoupon, couponApplied, couponErr } = useApp();

  const items = Object.entries(cart).map(([id, q]) => ({ product: findProduct(id), id, q }));
  const isEmpty = totals.count === 0;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>Shopping Cart</Text>
        <Text style={styles.countLabel}>{totals.count} {totals.count === 1 ? 'item' : 'items'}</Text>
      </View>

      {isEmpty ? (
        <View style={styles.emptyWrap}>
          <View style={styles.emptyIcon}>
            <IconCart size={46} color={colors.borderStrong} strokeWidth={1.5} />
          </View>
          <Text style={styles.emptyTitle}>Your cart is empty</Text>
          <Text style={styles.emptySub}>Add products from the catalogue to start your order.</Text>
          <PrimaryButton label="Browse Products" onPress={() => navigation.navigate('Main', { screen: 'Home' })} style={{ marginTop: 24, paddingHorizontal: 28, alignSelf: 'center', width: undefined, minWidth: 200 }} />
        </View>
      ) : (
        <ScrollView contentContainerStyle={{ paddingBottom: 20 }}>
          <View style={styles.itemsWrap}>
            {items.map(({ product, id, q }) => (
              <View key={id} style={styles.itemRow}>
                <PlaceholderImage seed={product.id} label={product.brand} radius={12} style={styles.itemImage} />
                <View style={{ flex: 1, minWidth: 0 }}>
                  <View style={styles.itemTopRow}>
                    <View style={{ minWidth: 0, flex: 1 }}>
                      <Text style={styles.itemBrand}>{product.brand.toUpperCase()}</Text>
                      <Text style={styles.itemName} numberOfLines={2}>{product.name}</Text>
                    </View>
                    <Pressable onPress={() => removeCart(id)} hitSlop={8}>
                      <IconTrash />
                    </Pressable>
                  </View>
                  <View style={styles.itemBottomRow}>
                    <Text style={styles.itemPrice}>{inr(product.price * q)}</Text>
                    <View style={styles.stepper}>
                      <Pressable style={styles.stepperBtn} onPress={() => decCart(id)}>
                        <IconMinus size={16} />
                      </Pressable>
                      <Text style={styles.stepperText}>{q}</Text>
                      <Pressable style={styles.stepperBtn} onPress={() => incCart(id)}>
                        <IconPlus size={15} color={colors.textSub} />
                      </Pressable>
                    </View>
                  </View>
                </View>
              </View>
            ))}
          </View>

          <View style={styles.couponBox}>
            <IconTicket />
            <TextInput
              value={couponCode}
              onChangeText={setCouponCode}
              placeholder="Enter coupon (try HC500)"
              placeholderTextColor={colors.textFaint}
              autoCapitalize="characters"
              style={styles.couponInput}
            />
            <Text style={styles.applyText} onPress={applyCoupon}>Apply</Text>
          </View>
          {!!couponErr && <Text style={styles.couponErr}>{couponErr}</Text>}
          {couponApplied && <Text style={styles.couponOk}>Coupon applied successfully</Text>}

          <View style={styles.billBox}>
            <Text style={styles.billTitle}>Bill Details</Text>
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>Subtotal</Text>
              <Text style={styles.billVal}>{inr(totals.sub)}</Text>
            </View>
            {totals.disc > 0 && (
              <View style={styles.billRow}>
                <Text style={styles.billLabel}>Coupon Discount</Text>
                <Text style={[styles.billVal, { color: colors.successDark }]}>-{inr(totals.disc)}</Text>
              </View>
            )}
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>GST (18%)</Text>
              <Text style={styles.billVal}>{inr(totals.gst)}</Text>
            </View>
            <View style={[styles.billRow, { marginBottom: 13 }]}>
              <Text style={styles.billLabel}>Delivery</Text>
              <Text style={[styles.billVal, { color: totals.deliv === 0 ? colors.successDark : colors.text }]}>
                {totals.deliv === 0 ? 'Free' : inr(totals.deliv)}
              </Text>
            </View>
            <View style={styles.billTotalRow}>
              <Text style={styles.billTotalLabel}>Total</Text>
              <Text style={styles.billTotalVal}>{inr(totals.total)}</Text>
            </View>
          </View>
        </ScrollView>
      )}

      {!isEmpty && (
        <SafeAreaView edges={['bottom']} style={styles.stickyBar}>
          <View>
            <Text style={styles.stickyLabel}>Total</Text>
            <Text style={styles.stickyVal}>{inr(totals.total)}</Text>
          </View>
          <Pressable style={styles.checkoutBtn} onPress={() => navigation.navigate('Checkout')}>
            <Text style={styles.checkoutText}>Checkout</Text>
            <IconChevronRight size={18} color="#fff" strokeWidth={2.2} />
          </Pressable>
        </SafeAreaView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bgAlt },
  header: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    paddingHorizontal: 20,
    paddingVertical: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  title: { fontSize: 21, fontFamily: fonts.displayExtra, color: colors.text, letterSpacing: -0.3 },
  countLabel: { fontSize: 13, color: colors.textMuted, fontFamily: fonts.bodyMedium },
  emptyWrap: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 40 },
  emptyIcon: { width: 104, height: 104, borderRadius: 52, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, alignItems: 'center', justifyContent: 'center', marginBottom: 24 },
  emptyTitle: { fontSize: 19, fontFamily: fonts.displayExtra, color: colors.text },
  emptySub: { fontSize: 13.5, color: colors.textMuted, marginTop: 8, textAlign: 'center', lineHeight: 20 },
  itemsWrap: { padding: 20, paddingBottom: 8, gap: 12 },
  itemRow: { flexDirection: 'row', gap: 12, borderWidth: 1, borderColor: colors.border, borderRadius: radii.xl, padding: 11, backgroundColor: '#fff' },
  itemImage: { width: 82, height: 82 },
  itemTopRow: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: 8 },
  itemBrand: { fontSize: 10, fontFamily: fonts.bodyBold, color: colors.textFaint, letterSpacing: 0.5 },
  itemName: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.text, lineHeight: 18, marginTop: 2 },
  itemBottomRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 8 },
  itemPrice: { fontSize: 16, fontFamily: fonts.displayExtra, color: colors.text },
  stepper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1.5, borderColor: colors.borderStrong, borderRadius: 10, height: 34 },
  stepperBtn: { width: 32, height: 34, alignItems: 'center', justifyContent: 'center' },
  stepperText: { width: 26, textAlign: 'center', fontSize: 14, fontFamily: fonts.bodyBold, color: colors.text },
  couponBox: {
    marginHorizontal: 20,
    marginTop: 4,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderStyle: 'dashed',
    borderColor: colors.accent,
    borderRadius: radii.lg,
    padding: 13,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  couponInput: { flex: 1, fontSize: 13.5, color: colors.text, padding: 0, textTransform: 'uppercase' },
  applyText: { fontSize: 13.5, fontFamily: fonts.bodyBold, color: colors.accent },
  couponErr: { marginHorizontal: 22, marginTop: 6, fontSize: 12, color: colors.danger, fontFamily: fonts.bodyMedium },
  couponOk: { marginHorizontal: 22, marginTop: 6, fontSize: 12, color: colors.successDark, fontFamily: fonts.bodySemibold },
  billBox: { margin: 20, marginTop: 16, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.xl, padding: 16 },
  billTitle: { fontSize: 14, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 13 },
  billRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 11 },
  billLabel: { fontSize: 13.5, color: colors.textMuted },
  billVal: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.text },
  billTotalRow: { borderTopWidth: 1, borderTopColor: colors.borderStrong, borderStyle: 'dashed', paddingTop: 13, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  billTotalLabel: { fontSize: 15, fontFamily: fonts.displayExtra, color: colors.text },
  billTotalVal: { fontSize: 19, fontFamily: fonts.displayExtra, color: colors.text },
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
  checkoutBtn: {
    flex: 1,
    height: 52,
    borderRadius: radii.md,
    backgroundColor: colors.accent,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  checkoutText: { fontSize: 15.5, fontFamily: fonts.bodyBold, color: '#fff' },
});
