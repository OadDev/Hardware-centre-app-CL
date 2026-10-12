import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts } from '../theme/theme';
import { ProductApi } from '../api/endpoints';
import { Product } from '../api/types';
import ProductCard from '../components/ProductCard';
import { BackButton } from '../components/UI';

type Props = NativeStackScreenProps<RootStackParamList, 'ProductList'>;

// "See all" on a home section should show the FULL matching catalog, not
// just the 4 items the home screen happens to preview — so each source maps
// to the real listing query instead of replaying that capped preview.
const SOURCE_QUERY: Record<'popular' | 'trending' | 'recommended' | 'deals', { sort?: string; tag?: string }> = {
  popular: { sort: 'pop' },
  trending: { sort: 'trending' },
  recommended: { sort: 'rating' },
  deals: { tag: 'Deal', sort: 'rating' },
};

export default function ProductListScreen({ navigation, route }: Props) {
  const { title } = route.params;
  const { wishlist, addToCart, toggleWish } = useApp();
  const [products, setProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    setProducts(null);
    const query = 'source' in route.params
      ? SOURCE_QUERY[route.params.source]
      : 'brand' in route.params
      ? { brands: route.params.brand }
      : { supplier: route.params.supplier };
    ProductApi.list(query)
      .then(setProducts)
      .catch(() => setProducts([]));
  }, [route.params]);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>{title}</Text>
      </View>

      {products === null ? (
        <View style={styles.center}>
          <ActivityIndicator color={colors.accent} />
        </View>
      ) : (
        <FlatList
          data={products}
          keyExtractor={(p) => p.id}
          numColumns={2}
          columnWrapperStyle={{ gap: 13, paddingHorizontal: 20 }}
          contentContainerStyle={{ gap: 13, paddingTop: 16, paddingBottom: 28 }}
          ListEmptyComponent={
            <View style={styles.center}>
              <Text style={styles.emptyTitle}>No products found</Text>
            </View>
          }
          renderItem={({ item }) => (
            <View style={{ flex: 1 }}>
              <ProductCard
                product={item}
                wished={!!wishlist[item.id]}
                onPress={() => navigation.navigate('ProductDetail', { id: item.id })}
                onAdd={() => addToCart(item.id, 1)}
                onToggleWish={() => toggleWish(item.id)}
              />
            </View>
          )}
        />
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
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingTop: 60 },
  emptyTitle: { fontSize: 15, fontFamily: fonts.bodyBold, color: colors.text },
});
