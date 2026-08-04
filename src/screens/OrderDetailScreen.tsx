import React, { useEffect, useState } from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { OrderApi } from '../api/endpoints';
import { Order } from '../api/types';
import { inr } from '../utils/format';
import { BackButton } from '../components/UI';
import { IconCheckCircle } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'OrderDetail'>;

const STAGE_COLORS: Record<Order['status'], { color: string; bg: string }> = {
  ongoing: { color: '#F59E0B', bg: '#FFFBEB' },
  delivered: { color: colors.success, bg: colors.successBg },
  cancelled: { color: colors.danger, bg: colors.dangerBg },
  returned: { color: colors.textMuted, bg: '#F3F4F6' },
};

export default function OrderDetailScreen({ navigation, route }: Props) {
  const { orderNo } = route.params;
  const { reorder, showToast } = useApp();
  const [order, setOrder] = useState<Order | null>(null);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    OrderApi.show(orderNo)
      .then(setOrder)
      .catch(() => showToast('Could not load order'));
  }, [orderNo]);

  if (!order) {
    return (
      <SafeAreaView style={[styles.container, { alignItems: 'center', justifyContent: 'center' }]} edges={['top']}>
        <ActivityIndicator color={colors.accent} />
      </SafeAreaView>
    );
  }

  const stage = STAGE_COLORS[order.status];
  const canReorder = order.status === 'delivered' || order.status === 'cancelled';

  const onReorder = async () => {
    setBusy(true);
    await reorder(order);
    setBusy(false);
    navigation.navigate('Main', { screen: 'Cart' });
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>{order.no}</Text>
        <View style={[styles.stagePill, { backgroundColor: stage.bg }]}>
          <Text style={[styles.stageText, { color: stage.color }]}>{order.stage}</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 40 }}>
        <View style={styles.card}>
          <Text style={styles.h2}>Order Status</Text>
          {order.timeline.map((step, i) => (
            <View key={step.title} style={styles.timelineRow}>
              <View style={styles.timelineDotCol}>
                <View
                  style={[
                    styles.timelineDot,
                    step.state === 'done' && { backgroundColor: colors.success },
                    step.state === 'current' && { backgroundColor: colors.accent },
                  ]}
                >
                  {step.state === 'done' && <IconCheckCircle size={12} color="#fff" strokeWidth={3} />}
                </View>
                {i < order.timeline.length - 1 && (
                  <View style={[styles.timelineLine, step.state === 'done' && { backgroundColor: colors.success }]} />
                )}
              </View>
              <View style={{ flex: 1, paddingBottom: 22 }}>
                <Text style={[styles.timelineTitle, step.state === 'pending' && { color: colors.textFaint }]}>{step.title}</Text>
                <Text style={styles.timelineTime}>{step.time}</Text>
              </View>
            </View>
          ))}
        </View>

        <View style={styles.card}>
          <Text style={styles.h2}>Items</Text>
          {order.items.map((it) => (
            <View key={it.id} style={styles.itemRow}>
              <View style={{ flex: 1, minWidth: 0 }}>
                <Text style={styles.itemName} numberOfLines={1}>{it.name}</Text>
                <Text style={styles.itemMeta}>{it.brand}{it.variant ? ` · ${it.variant}` : ''} · Qty {it.q}</Text>
              </View>
            </View>
          ))}
        </View>

        <View style={styles.card}>
          <Text style={styles.h2}>Payment Summary</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryVal}>{inr(order.subtotal)}</Text>
          </View>
          {order.discount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Discount</Text>
              <Text style={[styles.summaryVal, { color: colors.successDark }]}>-{inr(order.discount)}</Text>
            </View>
          )}
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>GST</Text>
            <Text style={styles.summaryVal}>{inr(order.gst)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery</Text>
            <Text style={styles.summaryVal}>{order.delivery === 0 ? 'Free' : inr(order.delivery)}</Text>
          </View>
          <View style={[styles.summaryRow, { marginTop: 6, paddingTop: 12, borderTopWidth: 1, borderTopColor: colors.borderStrong, borderStyle: 'dashed' }]}>
            <Text style={styles.summaryTotalLabel}>Total Paid</Text>
            <Text style={styles.summaryTotalVal}>{inr(order.total)}</Text>
          </View>
          <Text style={styles.paymentMeta}>
            {order.payment_method.toUpperCase()} · {order.payment_status} · {order.shipping_method === 'express' ? 'Express' : 'Standard'} delivery
          </Text>
        </View>

        {canReorder && (
          <View style={styles.reorderBtn}>
            <Text style={styles.reorderBtnText} onPress={busy ? undefined : onReorder}>
              {busy ? 'Adding to cart…' : 'Reorder'}
            </Text>
          </View>
        )}
      </ScrollView>
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
  topTitle: { flex: 1, fontSize: 16, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  stagePill: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  stageText: { fontSize: 11, fontFamily: fonts.bodyBold },
  card: { backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.xl, padding: 16, marginBottom: 14, ...shadow.card },
  h2: { fontSize: 15, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 14 },
  timelineRow: { flexDirection: 'row', gap: 12 },
  timelineDotCol: { alignItems: 'center' },
  timelineDot: { width: 22, height: 22, borderRadius: 11, backgroundColor: colors.borderStrong, alignItems: 'center', justifyContent: 'center' },
  timelineLine: { width: 2, flex: 1, backgroundColor: colors.border, marginTop: 2 },
  timelineTitle: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.text },
  timelineTime: { fontSize: 11.5, color: colors.textMuted, marginTop: 2 },
  itemRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 9, borderBottomWidth: 1, borderBottomColor: colors.divider },
  itemName: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.text },
  itemMeta: { fontSize: 11.5, color: colors.textMuted, marginTop: 3 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 9 },
  summaryLabel: { fontSize: 13, color: colors.textMuted },
  summaryVal: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.text },
  summaryTotalLabel: { fontSize: 14.5, fontFamily: fonts.displayExtra, color: colors.text },
  summaryTotalVal: { fontSize: 16, fontFamily: fonts.displayExtra, color: colors.text },
  paymentMeta: { fontSize: 11.5, color: colors.textFaint, marginTop: 8 },
  reorderBtn: { height: 52, borderRadius: radii.md, backgroundColor: colors.accent, alignItems: 'center', justifyContent: 'center' },
  reorderBtnText: { fontSize: 15, fontFamily: fonts.bodyBold, color: '#fff' },
});
