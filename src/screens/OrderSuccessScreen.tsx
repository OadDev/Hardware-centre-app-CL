import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { colors, fonts } from '../theme/theme';
import { PrimaryButton, SecondaryButton } from '../components/UI';
import { IconCheckCircle, IconTruck } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'OrderSuccess'>;

export default function OrderSuccessScreen({ navigation, route }: Props) {
  const { orderNo } = route.params;
  const scale = useRef(new Animated.Value(0.4)).current;

  useEffect(() => {
    Animated.spring(scale, { toValue: 1, useNativeDriver: true, friction: 5 }).start();
  }, []);

  const eta = new Date();
  eta.setDate(eta.getDate() + 5);
  const etaLabel = eta.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Animated.View style={[styles.circle, { transform: [{ scale }] }]}>
        <IconCheckCircle size={54} color="#fff" strokeWidth={3} />
      </Animated.View>
      <Text style={styles.title}>Order Placed!</Text>
      <Text style={styles.sub}>Your order has been confirmed and will be processed by the supplier shortly.</Text>

      <View style={styles.orderBox}>
        <Text style={styles.orderLabel}>ORDER NUMBER</Text>
        <Text style={styles.orderNo}>{orderNo}</Text>
      </View>

      <View style={styles.etaRow}>
        <IconTruck size={17} color={colors.successDark} strokeWidth={2} />
        <Text style={styles.etaText}>Estimated delivery by {etaLabel}</Text>
      </View>

      <View style={styles.buttons}>
        <PrimaryButton
          label="Track Order"
          onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main', params: { screen: 'Orders' } }] })}
        />
        <SecondaryButton
          label="Continue Shopping"
          onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main', params: { screen: 'Home' } }] })}
          style={{ marginTop: 12 }}
        />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 32 },
  circle: {
    width: 108,
    height: 108,
    borderRadius: 54,
    backgroundColor: colors.success,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: { fontSize: 24, fontFamily: fonts.displayExtra, color: colors.text, marginTop: 28, letterSpacing: -0.4 },
  sub: { fontSize: 14, color: colors.textMuted, marginTop: 10, textAlign: 'center', lineHeight: 21, maxWidth: 260 },
  orderBox: {
    marginTop: 24,
    backgroundColor: colors.bgAlt,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 14,
    paddingHorizontal: 22,
    paddingVertical: 16,
    alignItems: 'center',
    gap: 4,
  },
  orderLabel: { fontSize: 11, color: colors.textFaint, fontFamily: fonts.bodySemibold, letterSpacing: 0.6 },
  orderNo: { fontSize: 18, fontFamily: fonts.displayExtra, color: colors.text },
  etaRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 18 },
  etaText: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.successDark },
  buttons: { width: '100%', marginTop: 34 },
});
