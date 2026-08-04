import React, { useCallback, useState } from 'react';
import { ActivityIndicator, Dimensions, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts } from '../theme/theme';
import { WishlistApi } from '../api/endpoints';
import { Product } from '../api/types';
import ProductCard from '../components/ProductCard';
import { BackButton } from '../components/UI';
import { IconWishlist } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;
const { width: SCREEN_W } = Dimensions.get('window');
const CARD_W = (SCREEN_W - 40 - 13) / 2;

export default function WishlistScreen() {
  const navigation = useNavigation<Nav>();
  const { wishlist, addToCart, toggleWish } = useApp();
  const [products, setProducts] = useState<Product[] | null>(null);

  const load = useCallback(() => {
    WishlistApi.list()
      .then(setProducts)
      .catch(() => setProducts([]));
  }, []);

  useFocusEffect(
    React.useCallback(() => {
      load();
    }, [load])
  );

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>My Wishlist</Text>
      </View>

      {products === null ? (
        <View style={styles.center}>
          <ActivityIndicator color={colors.accent} />
        </View>
      ) : products.length === 0 ? (
        <View style={styles.center}>
          <IconWishlist size={40} color={colors.textFaint} />
          <Text style={styles.emptyTitle}>Your wishlist is empty</Text>
          <Text style={styles.emptySub}>Tap the heart on any product to save it here.</Text>
        </View>
      ) : (
        <View style={styles.grid}>
          {products.map((p) => (
            <ProductCard
              key={p.id}
              product={p}
              width={CARD_W}
              wished={!!wishlist[p.id]}
              onPress={() => navigation.navigate('ProductDetail', { id: p.id })}
              onAdd={() => addToCart(p.id, 1)}
              onToggleWish={() => {
                toggleWish(p.id);
                setProducts((prev) => (prev ? prev.filter((x) => x.id !== p.id) : prev));
              }}
            />
          ))}
        </View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  topTitle: { flex: 1, fontSize: 17, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 40, gap: 8 },
  emptyTitle: { fontSize: 16, fontFamily: fonts.bodyBold, color: colors.text, marginTop: 8 },
  emptySub: { fontSize: 13, color: colors.textMuted, textAlign: 'center' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 13, padding: 20 },
});
