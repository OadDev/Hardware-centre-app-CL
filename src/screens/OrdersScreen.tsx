import React, { useMemo, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { OrderStatus } from '../context/types';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { inr } from '../utils/format';
import PlaceholderImage from '../components/PlaceholderImage';
import { IconEmptyOrders } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const TABS: { key: OrderStatus; label: string }[] = [
  { key: 'ongoing', label: 'Ongoing' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'cancelled', label: 'Cancelled' },
  { key: 'returned', label: 'Returned' },
];

const STAGE_COLORS: Record<OrderStatus, { color: string; bg: string }> = {
  ongoing: { color: '#F59E0B', bg: '#FFFBEB' },
  delivered: { color: colors.success, bg: colors.successBg },
  cancelled: { color: colors.danger, bg: colors.dangerBg },
  returned: { color: colors.textMuted, bg: '#F3F4F6' },
};

const ACTION_LABEL: Record<OrderStatus, string> = {
  ongoing: 'Track Order',
  delivered: 'Reorder',
  cancelled: 'Reorder',
  returned: 'View Details',
};

export default function OrdersScreen() {
  const navigation = useNavigation<Nav>();
  const { orders, reorder, showToast } = useApp();
  const [tab, setTab] = useState<OrderStatus>('ongoing');

  const filtered = useMemo(() => orders.filter((o) => o.status === tab), [orders, tab]);

  const runAction = (order: (typeof orders)[number]) => {
    if (order.status === 'delivered' || order.status === 'cancelled') {
      reorder(order).then(() => navigation.navigate('Main', { screen: 'Cart' }));
    } else if (order.status === 'ongoing') {
      showToast('Tracking ' + order.no);
    } else {
      showToast('Order ' + order.no);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>My Orders</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingBottom: 12 }}>
          {TABS.map((t) => {
            const active = t.key === tab;
            return (
              <Pressable key={t.key} onPress={() => setTab(t.key)} style={[styles.tabChip, active ? styles.tabChipActive : null]}>
                <Text style={[styles.tabChipText, active ? styles.tabChipTextActive : null]}>{t.label}</Text>
              </Pressable>
            );
          })}
        </ScrollView>
      </View>

      {filtered.length === 0 ? (
        <View style={styles.emptyWrap}>
          <View style={styles.emptyIcon}>
            <IconEmptyOrders />
          </View>
          <Text style={styles.emptyTitle}>No orders here yet</Text>
          <Text style={styles.emptySub}>Orders in this category will appear here.</Text>
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.list}>
          {filtered.map((o) => {
            const first = o.items[0];
            const count = o.items.reduce((a, it) => a + it.q, 0);
            const stage = STAGE_COLORS[o.status];
            return (
              <View key={o.no} style={styles.card}>
                <View style={styles.cardTopRow}>
                  <Text style={styles.orderNo}>{o.no}</Text>
                  <View style={[styles.stagePill, { backgroundColor: stage.bg }]}>
                    <Text style={[styles.stageText, { color: stage.color }]}>{o.stage}</Text>
                  </View>
                </View>
                <Text style={styles.dateText}>{o.date}</Text>
                <View style={styles.itemRow}>
                  <PlaceholderImage seed={first.id} label={first.brand} radius={11} style={styles.itemImage} />
                  <View style={{ flex: 1, minWidth: 0 }}>
                    <Text style={styles.itemTitle} numberOfLines={1}>{first.name}</Text>
                    <Text style={styles.itemSub}>{count > 1 ? `${count} items` : `${count} item`} · {o.sub}</Text>
                  </View>
                </View>
                <View style={styles.cardBottomRow}>
                  <Text style={styles.totalText}>
                    Total <Text style={styles.totalVal}>{inr(o.total)}</Text>
                  </Text>
                  <Pressable style={styles.actionBtn} onPress={() => runAction(o)}>
                    <Text style={styles.actionText}>{ACTION_LABEL[o.status]}</Text>
                  </Pressable>
                </View>
              </View>
            );
          })}
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bgAlt },
  header: { backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: colors.border, paddingHorizontal: 20, paddingTop: 14 },
  title: { fontSize: 21, fontFamily: fonts.displayExtra, color: colors.text, letterSpacing: -0.3, marginBottom: 14 },
  tabChip: { height: 36, paddingHorizontal: 16, borderRadius: radii.round, borderWidth: 1, borderColor: colors.borderStrong, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' },
  tabChipActive: { backgroundColor: colors.bgDark, borderColor: colors.bgDark },
  tabChipText: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.textMuted },
  tabChipTextActive: { color: '#fff' },
  emptyWrap: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 40 },
  emptyIcon: { width: 104, height: 104, borderRadius: 52, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, alignItems: 'center', justifyContent: 'center', marginBottom: 22 },
  emptyTitle: { fontSize: 18, fontFamily: fonts.displayExtra, color: colors.text },
  emptySub: { fontSize: 13.5, color: colors.textMuted, marginTop: 8, textAlign: 'center', lineHeight: 20 },
  list: { padding: 20, gap: 13 },
  card: { backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.xl, padding: 15, ...shadow.card },
  cardTopRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  orderNo: { fontSize: 13.5, fontFamily: fonts.displayExtra, color: colors.text },
  stagePill: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  stageText: { fontSize: 11, fontFamily: fonts.bodyBold },
  dateText: { fontSize: 11.5, color: colors.textFaint, marginTop: 4 },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: 13 },
  itemImage: { width: 54, height: 54 },
  itemTitle: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.text, lineHeight: 18 },
  itemSub: { fontSize: 11.5, color: colors.textMuted, marginTop: 3 },
  cardBottomRow: { borderTopWidth: 1, borderTopColor: colors.divider, marginTop: 13, paddingTop: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  totalText: { fontSize: 11, color: colors.textMuted },
  totalVal: { fontSize: 15, fontFamily: fonts.displayExtra, color: colors.text },
  actionBtn: { height: 36, paddingHorizontal: 16, borderWidth: 1.5, borderColor: colors.bgDark, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
  actionText: { fontSize: 12.5, fontFamily: fonts.bodyBold, color: colors.bgDark },
});
