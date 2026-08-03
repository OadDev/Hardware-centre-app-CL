import React, { useRef } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { WebView, WebViewMessageEvent } from 'react-native-webview';
import { SafeAreaView } from 'react-native-safe-area-context';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { API_BASE_URL } from '../api/config';
import { colors } from '../theme/theme';
import { BackButton } from '../components/UI';

type Props = NativeStackScreenProps<RootStackParamList, 'RazorpayCheckout'>;

const SITE_ROOT = API_BASE_URL.replace(/\/api\/v1\/?$/, '');

type BridgeMessage =
  | { status: 'success'; razorpay_order_id: string; razorpay_payment_id: string; razorpay_signature: string }
  | { status: 'cancelled' }
  | { status: 'failed'; message?: string };

export default function RazorpayCheckoutScreen({ navigation, route }: Props) {
  const { key_id, gateway_order_id, amount, currency } = route.params;
  const { placeOrder, showToast } = useApp();
  const handled = useRef(false);

  const bridgeUrl =
    `${SITE_ROOT}/checkout/razorpay-bridge` +
    `?key=${encodeURIComponent(key_id)}` +
    `&order_id=${encodeURIComponent(gateway_order_id)}` +
    `&amount=${encodeURIComponent(String(amount))}` +
    `&currency=${encodeURIComponent(currency)}`;

  const onMessage = async (event: WebViewMessageEvent) => {
    if (handled.current) return;

    let data: BridgeMessage;
    try {
      data = JSON.parse(event.nativeEvent.data);
    } catch {
      return;
    }

    if (data.status === 'success') {
      handled.current = true;
      try {
        const orderNo = await placeOrder({
          gateway_order_id: data.razorpay_order_id,
          gateway_payment_id: data.razorpay_payment_id,
          gateway_signature: data.razorpay_signature,
        });
        navigation.replace('OrderSuccess', { orderNo });
      } catch (err) {
        showToast(err instanceof Error ? err.message : 'Could not confirm the order');
        navigation.goBack();
      }
    } else if (data.status === 'cancelled') {
      handled.current = true;
      navigation.goBack();
    } else if (data.status === 'failed') {
      handled.current = true;
      showToast(data.message || 'Payment failed. Please try again.');
      navigation.goBack();
    }
  };

  return (
    <View style={styles.container}>
      <SafeAreaView edges={['top']} style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
      </SafeAreaView>
      <WebView
        source={{ uri: bridgeUrl }}
        onMessage={onMessage}
        startInLoadingState
        renderLoading={() => (
          <View style={styles.loading}>
            <ActivityIndicator color={colors.accent} />
          </View>
        )}
        style={styles.webview}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },
  topBar: { paddingHorizontal: 14, paddingVertical: 12 },
  webview: { flex: 1 },
  loading: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fff',
  },
});
