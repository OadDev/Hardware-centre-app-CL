import React, { useMemo, useState } from 'react';
import { Dimensions, FlatList, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { HOME_SECTIONS, PRODUCTS, byIds } from '../data/catalog';
import ProductCard from '../components/ProductCard';
import { BackButton } from '../components/UI';
import { IconBoltFilled, IconClock, IconMic, IconSearch } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'Search'>;

const POPULAR_TERMS = ['Power Tools', 'PVC Fittings', 'Cement', 'Hand Gloves'];
const { width: SCREEN_W } = Dimensions.get('window');
const CARD_W = (SCREEN_W - 40 - 13) / 2;

export default function SearchScreen({ navigation }: Props) {
  const { wishlist, addToCart, toggleWish } = useApp();
  const [query, setQuery] = useState('');
  const [recent, setRecent] = useState(['impact drill', 'cpvc pipe', 'havells wire', 'safety helmet']);

  const trending = byIds(HOME_SECTIONS.trending);

  const results = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return null;
    return PRODUCTS.filter((p) => p.name.toLowerCase().includes(q) || p.brand.toLowerCase().includes(q) || p.cat.toLowerCase().includes(q));
  }, [query]);

  const runSearch = (term: string) => {
    setQuery(term);
    setRecent((r) => [term, ...r.filter((x) => x !== term)].slice(0, 6));
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.searchRow}>
        <BackButton onPress={() => navigation.goBack()} />
        <View style={styles.searchBar}>
          <IconSearch size={19} color={colors.textMuted} />
          <TextInput
            autoFocus
            value={query}
            onChangeText={setQuery}
            onSubmitEditing={() => query.trim() && runSearch(query.trim())}
            placeholder="Search 8,000+ products"
            placeholderTextColor={colors.textFaint}
            style={styles.input}
          />
          <IconMic size={18} />
        </View>
      </View>

      {results ? (
        <FlatList
          data={results}
          keyExtractor={(p) => p.id}
          numColumns={2}
          columnWrapperStyle={{ gap: 13, paddingHorizontal: 20 }}
          contentContainerStyle={{ gap: 13, paddingBottom: 28, paddingTop: 4 }}
          ListEmptyComponent={
            <View style={styles.empty}>
              <Text style={styles.emptyTitle}>No results for "{query}"</Text>
              <Text style={styles.emptySub}>Try a different keyword or browse categories.</Text>
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
      ) : (
        <ScrollView contentContainerStyle={{ paddingHorizontal: 20, paddingBottom: 28 }}>
          <View style={styles.rowBetween}>
            <Text style={styles.sectionLabel}>Recent Searches</Text>
            <Text style={styles.clearAll} onPress={() => setRecent([])}>
              Clear all
            </Text>
          </View>
          <View style={styles.chipWrap}>
            {recent.map((term) => (
              <Pressable key={term} style={styles.chip} onPress={() => runSearch(term)}>
                <IconClock size={14} />
                <Text style={styles.chipText}>{term}</Text>
              </Pressable>
            ))}
          </View>

          <Text style={[styles.sectionLabel, { marginBottom: 12 }]}>Popular Searches</Text>
          <View style={[styles.chipWrap, { marginBottom: 28 }]}>
            {POPULAR_TERMS.map((term) => (
              <Pressable key={term} style={styles.chipAccent} onPress={() => runSearch(term)}>
                <IconBoltFilled size={13} />
                <Text style={styles.chipAccentText}>{term}</Text>
              </Pressable>
            ))}
          </View>

          <Text style={styles.trendingLabel}>Trending Products</Text>
          <View style={styles.grid}>
            {trending.map((p) => (
              <ProductCard
                key={p.id}
                product={p}
                width={CARD_W}
                wished={!!wishlist[p.id]}
                onPress={() => navigation.navigate('ProductDetail', { id: p.id })}
                onAdd={() => addToCart(p.id, 1)}
                onToggleWish={() => toggleWish(p.id)}
              />
            ))}
          </View>
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  searchRow: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 20, marginVertical: 8, marginBottom: 18 },
  searchBar: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    height: 48,
    borderWidth: 1.5,
    borderColor: colors.accent,
    borderRadius: radii.lg,
    paddingHorizontal: 14,
  },
  input: { flex: 1, fontSize: 14.5, color: colors.text, padding: 0 },
  rowBetween: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 },
  sectionLabel: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.text },
  clearAll: { fontSize: 12.5, fontFamily: fonts.bodySemibold, color: colors.accent },
  chipWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 9, marginBottom: 26 },
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 7,
    paddingHorizontal: 13,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: colors.borderStrong,
    borderRadius: radii.round,
    backgroundColor: colors.bgAlt,
  },
  chipText: { fontSize: 13, color: colors.textSub, fontFamily: fonts.bodyMedium },
  chipAccent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 13,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: colors.accentBorder,
    borderRadius: radii.round,
    backgroundColor: colors.accentLight,
  },
  chipAccentText: { fontSize: 13, color: colors.accentDark, fontFamily: fonts.bodySemibold },
  trendingLabel: { fontSize: 17, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 14 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 13 },
  empty: { paddingTop: 60, alignItems: 'center', paddingHorizontal: 30 },
  emptyTitle: { fontSize: 15, fontFamily: fonts.bodyBold, color: colors.text, textAlign: 'center' },
  emptySub: { fontSize: 13, color: colors.textMuted, marginTop: 8, textAlign: 'center' },
});
