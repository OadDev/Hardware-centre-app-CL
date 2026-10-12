import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Dimensions, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { CategoryApi } from '../api/endpoints';
import { Product } from '../api/types';
import { discountPct, inr } from '../utils/format';
import ProductCard from '../components/ProductCard';
import PlaceholderImage from '../components/PlaceholderImage';
import { BackButton } from '../components/UI';
import { IconCart, IconGrid, IconListView, IconSearch, IconStar } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'CategoryListing'>;
type SortKey = 'pop' | 'low' | 'high' | 'rating';
const SORTS: { key: SortKey; label: string }[] = [
  { key: 'pop', label: 'Popular' },
  { key: 'low', label: 'Price: Low' },
  { key: 'high', label: 'Price: High' },
  { key: 'rating', label: 'Top Rated' },
];

const { width: SCREEN_W } = Dimensions.get('window');

export default function CategoryListingScreen({ navigation, route }: Props) {
  const { catId } = route.params;
  const { wishlist, addToCart, toggleWish, totals, categories } = useApp();
  const [view, setView] = useState<'grid' | 'list'>('grid');
  const [sort, setSort] = useState<SortKey>('pop');
  const [title, setTitle] = useState('');
  const [listing, setListing] = useState<Product[] | null>(null);
  const [activeCatId, setActiveCatId] = useState(catId);

  const subcategories = categories.filter((c) => c.parent_id === catId);

  useEffect(() => {
    setActiveCatId(catId);
  }, [catId]);

  useEffect(() => {
    let cancelled = false;
    setListing(null);
    CategoryApi.products(activeCatId, sort)
      .then((res) => {
        if (cancelled) return;
        setTitle(res.category.name);
        setListing(res.products);
      })
      .catch(() => {
        if (cancelled) return;
        setTitle('Products');
        setListing([]);
      });
    return () => {
      cancelled = true;
    };
  }, [activeCatId, sort]);

  const cardW = (SCREEN_W - 40 - 13) / 2;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => (navigation.canGoBack() ? navigation.goBack() : navigation.navigate('Main', { screen: 'Categories' }))} />
        <Text style={styles.topTitle}>{title || 'Products'}</Text>
        <Pressable style={styles.iconBtnSmall} onPress={() => navigation.navigate('Search')}>
          <IconSearch size={20} color={colors.bgDark} />
        </Pressable>
        <Pressable style={styles.iconBtnSmall} onPress={() => navigation.navigate('Main', { screen: 'Cart' })}>
          <IconCart size={21} />
          {totals.count > 0 && (
            <View style={styles.cartBadge}>
              <Text style={styles.cartBadgeText}>{totals.count}</Text>
            </View>
          )}
        </Pressable>
      </View>

      {listing === null ? (
        <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center' }}>
          <ActivityIndicator color={colors.accent} />
        </View>
      ) : (
      <ScrollView contentContainerStyle={{ paddingBottom: 24 }}>
        <View style={{ paddingHorizontal: 20, paddingTop: 14 }}>
          <View style={styles.banner}>
            <View style={styles.bannerOverlay}>
              <Text style={styles.bannerTitle}>{title}</Text>
              <Text style={styles.bannerSub}>Verified suppliers · Best trade prices</Text>
            </View>
          </View>
        </View>

        <View style={styles.controlsRow}>
          <Text style={styles.countLabel}>{listing.length} products</Text>
          <View style={styles.controlsRight}>
            <View style={styles.viewToggle}>
              <Pressable
                style={[styles.viewBtn, view === 'grid' ? styles.viewBtnActive : null]}
                onPress={() => setView('grid')}
              >
                <IconGrid size={16} color={view === 'grid' ? '#fff' : colors.textMuted} />
              </Pressable>
              <Pressable
                style={[styles.viewBtn, view === 'list' ? styles.viewBtnActive : null]}
                onPress={() => setView('list')}
              >
                <IconListView size={16} color={view === 'list' ? '#fff' : colors.textMuted} />
              </Pressable>
            </View>
          </View>
        </View>

        {subcategories.length > 0 && (
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingLeft: 20 }} contentContainerStyle={{ gap: 8, paddingRight: 20 }}>
            <Pressable
              onPress={() => setActiveCatId(catId)}
              style={[styles.chip, activeCatId === catId ? styles.chipActive : null]}
            >
              <Text style={[styles.chipText, activeCatId === catId ? styles.chipTextActive : null]}>All</Text>
            </Pressable>
            {subcategories.map((sub) => (
              <Pressable
                key={sub.id}
                onPress={() => setActiveCatId(sub.id)}
                style={[styles.chip, activeCatId === sub.id ? styles.chipActive : null]}
              >
                <Text style={[styles.chipText, activeCatId === sub.id ? styles.chipTextActive : null]}>{sub.name}</Text>
              </Pressable>
            ))}
          </ScrollView>
        )}

        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingLeft: 20 }} contentContainerStyle={{ gap: 8, paddingRight: 20, paddingVertical: 6 }}>
          {SORTS.map((s) => (
            <Pressable
              key={s.key}
              onPress={() => setSort(s.key)}
              style={[styles.chip, sort === s.key ? styles.chipActive : null]}
            >
              <Text style={[styles.chipText, sort === s.key ? styles.chipTextActive : null]}>{s.label}</Text>
            </Pressable>
          ))}
        </ScrollView>

        {view === 'grid' ? (
          <View style={styles.grid}>
            {listing.map((p) => (
              <ProductCard
                key={p.id}
                product={p}
                width={cardW}
                wished={!!wishlist[p.id]}
                onPress={() => navigation.navigate('ProductDetail', { id: p.id })}
                onAdd={() => addToCart(p.id, 1)}
                onToggleWish={() => toggleWish(p.id)}
              />
            ))}
          </View>
        ) : (
          <View style={styles.list}>
            {listing.map((p) => {
              const off = discountPct(p.price, p.mrp);
              return (
                <Pressable key={p.id} style={styles.listRow} onPress={() => navigation.navigate('ProductDetail', { id: p.id })}>
                  {p.images?.[0] ? (
                    <Image source={{ uri: p.images[0] }} style={styles.listImage} resizeMode="cover" />
                  ) : (
                    <PlaceholderImage seed={p.id} label={p.brand} radius={12} style={styles.listImage} />
                  )}
                  <View style={{ flex: 1, minWidth: 0 }}>
                    <Text style={styles.listBrand}>{p.brand.toUpperCase()}</Text>
                    <Text style={styles.listName} numberOfLines={1}>{p.name}</Text>
                    <View style={styles.listRatingRow}>
                      <IconStar size={12} />
                      <Text style={styles.listRating}>{p.rating.toFixed(1)}</Text>
                      <Text style={styles.listSupplier}>· {p.supplier}</Text>
                    </View>
                    <View style={styles.listBottomRow}>
                      <View style={styles.listPriceRow}>
                        <Text style={styles.listPrice}>{inr(p.price)}</Text>
                        {off > 0 && <Text style={styles.listMrp}>{inr(p.mrp)}</Text>}
                      </View>
                      <Pressable style={styles.addBtn} onPress={() => addToCart(p.id, 1)}>
                        <Text style={styles.addBtnText}>Add</Text>
                      </Pressable>
                    </View>
                  </View>
                </Pressable>
              );
            })}
          </View>
        )}
      </ScrollView>
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
  iconBtnSmall: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  topTitle: { flex: 1, fontSize: 17, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  cartBadge: {
    position: 'absolute',
    top: 5,
    right: 5,
    minWidth: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: colors.accent,
    borderWidth: 1.5,
    borderColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  cartBadgeText: { fontSize: 9.5, fontFamily: fonts.bodyBold, color: '#fff' },
  banner: { height: 100, borderRadius: radii.xl, overflow: 'hidden', backgroundColor: colors.bgDark },
  bannerOverlay: { flex: 1, padding: 18, justifyContent: 'center', backgroundColor: 'rgba(15,20,28,0.5)' },
  bannerTitle: { fontSize: 19, fontFamily: fonts.displayExtra, color: '#fff' },
  bannerSub: { fontSize: 12, color: '#D1D5DB', marginTop: 4 },
  controlsRow: { paddingHorizontal: 20, paddingTop: 16, paddingBottom: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  countLabel: { fontSize: 13, color: colors.textMuted, fontFamily: fonts.bodyMedium },
  controlsRight: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  viewToggle: { flexDirection: 'row', borderWidth: 1, borderColor: colors.borderStrong, borderRadius: radii.md, overflow: 'hidden' },
  viewBtn: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  viewBtnActive: { backgroundColor: colors.bgDark },
  chip: { height: 34, paddingHorizontal: 14, borderWidth: 1, borderColor: colors.borderStrong, borderRadius: radii.round, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' },
  chipActive: { backgroundColor: colors.bgDark, borderColor: colors.bgDark },
  chipText: { fontSize: 12.5, fontFamily: fonts.bodySemibold, color: colors.textSub },
  chipTextActive: { color: '#fff' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 13, paddingHorizontal: 20, paddingTop: 8 },
  list: { paddingHorizontal: 20, paddingTop: 8, gap: 12 },
  listRow: {
    flexDirection: 'row',
    gap: 13,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.xl,
    padding: 11,
    backgroundColor: '#fff',
    ...shadow.card,
  },
  listImage: { width: 96, height: 96, borderRadius: 12 },
  listBrand: { fontSize: 10, fontFamily: fonts.bodyBold, color: colors.textFaint, letterSpacing: 0.5 },
  listName: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.text, marginTop: 2 },
  listRatingRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 5 },
  listRating: { fontSize: 11, fontFamily: fonts.bodySemibold, color: colors.textSub },
  listSupplier: { fontSize: 10.5, color: colors.textFaint },
  listBottomRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 8 },
  listPriceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 7 },
  listPrice: { fontSize: 16, fontFamily: fonts.displayExtra, color: colors.text },
  listMrp: { fontSize: 11, color: colors.textFaint, textDecorationLine: 'line-through' },
  addBtn: { height: 32, paddingHorizontal: 14, borderRadius: 9, backgroundColor: colors.accent, alignItems: 'center', justifyContent: 'center' },
  addBtnText: { fontSize: 12.5, fontFamily: fonts.bodyBold, color: '#fff' },
});
