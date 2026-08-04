import React, { useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Dimensions,
  FlatList,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { initials } from '../utils/format';
import ProductCard from '../components/ProductCard';
import { IconBell, IconBolt, IconChevronDown, IconLocation, IconMic, IconSearch, IconStar, IconVerified } from '../components/Icons';
import { iconForCat } from '../components/CategoryIcon';
import { SectionHeader } from '../components/UI';

const { width: SCREEN_W } = Dimensions.get('window');

function useCountdown(seconds: number) {
  const [remaining, setRemaining] = useState(seconds);
  useEffect(() => {
    const t = setInterval(() => setRemaining((r) => (r > 0 ? r - 1 : seconds)), 1000);
    return () => clearInterval(t);
  }, []);
  const h = Math.floor(remaining / 3600);
  const m = Math.floor((remaining % 3600) / 60);
  const s = remaining % 60;
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function HomeScreen() {
  const navigation = useNavigation<Nav>();
  const { wishlist, addToCart, toggleWish, user, home, categories, showToast } = useApp();
  const [bannerIdx, setBannerIdx] = useState(0);
  const bannerRef = useRef<FlatList>(null);
  const countdown = useCountdown(23091);

  const banners = home?.banners || [];

  useEffect(() => {
    if (banners.length === 0) return;
    const t = setInterval(() => {
      setBannerIdx((i) => {
        const next = (i + 1) % banners.length;
        bannerRef.current?.scrollToOffset({ offset: next * (SCREEN_W - 40), animated: true });
        return next;
      });
    }, 3800);
    return () => clearInterval(t);
  }, [banners.length]);

  const goProduct = (id: string) => navigation.navigate('ProductDetail', { id });
  const goCat = (id: string) => navigation.navigate('CategoryListing', { catId: id });

  if (!home) {
    return (
      <SafeAreaView style={[styles.container, { alignItems: 'center', justifyContent: 'center' }]}>
        <ActivityIndicator color={colors.accent} />
      </SafeAreaView>
    );
  }

  const popular = home.popular;
  const deals = home.deals;
  const trending = home.trending;
  const recommended = home.recommended;
  const suppliers = home.suppliers;
  const brands = home.brands;
  const topCats = categories.filter((c) => !c.parent_id).slice(0, 7);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 24 }}>
        {/* header */}
        <View style={styles.header}>
          <View style={styles.deliverRow}>
            <IconLocation size={14} />
            <Text style={styles.deliverLabel}>Deliver to</Text>
            <Text style={styles.deliverValue}>New Delhi 110006</Text>
            <IconChevronDown size={14} />
          </View>
          <View style={styles.greetRow}>
            <View style={styles.greetLeft}>
              <View style={styles.avatar}>
                <Text style={styles.avatarText}>{user.name[0]}</Text>
              </View>
              <View>
                <Text style={styles.greetSmall}>Good morning,</Text>
                <Text style={styles.greetName}>{user.name}</Text>
              </View>
            </View>
            <Pressable style={styles.bellBtn} onPress={() => showToast('No new notifications')}>
              <IconBell />
            </Pressable>
          </View>
          <Pressable style={styles.searchBar} onPress={() => navigation.navigate('Search')}>
            <IconSearch />
            <Text style={styles.searchPlaceholder}>Search tools, brands, suppliers…</Text>
            <IconMic />
          </Pressable>
        </View>

        {/* banner carousel */}
        <View style={{ paddingHorizontal: 20 }}>
          <View style={styles.bannerWrap}>
            <FlatList
              ref={bannerRef}
              data={banners}
              horizontal
              pagingEnabled
              showsHorizontalScrollIndicator={false}
              keyExtractor={(b) => String(b.id)}
              onMomentumScrollEnd={(e) => {
                const i = Math.round(e.nativeEvent.contentOffset.x / (SCREEN_W - 40));
                setBannerIdx(i);
              }}
              renderItem={({ item }) => (
                <View style={[styles.banner, { width: SCREEN_W - 40, backgroundColor: item.bg }]}>
                  <View style={styles.bannerOverlay}>
                    <Text style={styles.bannerBadge}>{item.badge}</Text>
                    <Text style={styles.bannerTitle}>{item.title}</Text>
                    <Text style={styles.bannerSub}>{item.sub}</Text>
                  </View>
                </View>
              )}
            />
            <View style={styles.bannerDots}>
              {banners.map((_, i) => (
                <View key={i} style={[styles.bannerDot, i === bannerIdx ? styles.bannerDotActive : null]} />
              ))}
            </View>
          </View>
        </View>

        {/* categories */}
        <SectionHeader title="Shop by Category" action="See all" onAction={() => navigation.navigate('Main', { screen: 'Categories' })} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingLeft: 20 }} contentContainerStyle={{ paddingRight: 20, gap: 16 }}>
          {topCats.map((c) => (
            <Pressable key={c.id} style={styles.catItem} onPress={() => goCat(c.id)}>
              <View style={styles.catIconBox}>{iconForCat(c.id)}</View>
              <Text style={styles.catLabel}>{c.name}</Text>
            </Pressable>
          ))}
        </ScrollView>

        {/* popular */}
        <SectionHeader title="Popular Products" action="See all" onAction={() => navigation.navigate('ProductList', { title: 'Popular Products', source: 'popular' })} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingLeft: 20 }} contentContainerStyle={{ paddingRight: 20, gap: 13 }}>
          {popular.map((p) => (
            <ProductCard
              key={p.id}
              product={p}
              width={154}
              wished={!!wishlist[p.id]}
              onPress={() => goProduct(p.id)}
              onAdd={() => addToCart(p.id, 1)}
              onToggleWish={() => toggleWish(p.id)}
            />
          ))}
        </ScrollView>

        {/* featured brands */}
        <SectionHeader title="Featured Brands" />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingLeft: 20 }} contentContainerStyle={{ paddingRight: 20, gap: 11 }}>
          {brands.map((b) => (
            <View key={b} style={styles.brandCard}>
              <View style={styles.brandIcon}>
                <Text style={styles.brandIconText}>{initials(b)}</Text>
              </View>
              <Text style={styles.brandName}>{b}</Text>
            </View>
          ))}
        </ScrollView>

        {/* deals */}
        <View style={styles.dealsCard}>
          <View style={styles.dealsHeader}>
            <View style={styles.dealsTitleRow}>
              <IconBolt size={20} color={colors.accent} strokeWidth={1.9} />
              <Text style={styles.dealsTitle}>Deals of the Day</Text>
            </View>
            <Text style={styles.dealsEnds}>Ends in {countdown}</Text>
          </View>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 12 }}>
            {deals.map((p) => (
              <ProductCard
                key={p.id}
                product={p}
                width={150}
                wished={!!wishlist[p.id]}
                onPress={() => goProduct(p.id)}
                onAdd={() => addToCart(p.id, 1)}
                onToggleWish={() => toggleWish(p.id)}
              />
            ))}
          </ScrollView>
        </View>

        {/* trending */}
        <SectionHeader title="Trending Now" action="See all" onAction={() => navigation.navigate('ProductList', { title: 'Trending Now', source: 'trending' })} />
        <View style={styles.grid}>
          {trending.map((p) => (
            <ProductCard
              key={p.id}
              product={p}
              width={(SCREEN_W - 40 - 13) / 2}
              wished={!!wishlist[p.id]}
              onPress={() => goProduct(p.id)}
              onAdd={() => addToCart(p.id, 1)}
              onToggleWish={() => toggleWish(p.id)}
            />
          ))}
        </View>

        {/* suppliers */}
        <SectionHeader title="Top Suppliers" action="See all" onAction={() => navigation.navigate('Suppliers')} />
        <View style={{ paddingHorizontal: 20, gap: 11 }}>
          {suppliers.map((s) => (
            <Pressable
              key={s.id}
              style={styles.supplierRow}
              onPress={() => navigation.navigate('ProductList', { title: s.name, supplier: s.name })}
            >
              <View style={styles.supplierIcon}>
                <IconVerified size={22} color={colors.bgDark} />
              </View>
              <View style={{ flex: 1, minWidth: 0 }}>
                <View style={styles.supplierNameRow}>
                  <Text style={styles.supplierName}>{s.name}</Text>
                  <IconVerified />
                </View>
                <Text style={styles.supplierMeta}>
                  {s.loc} · {s.years} yrs · GST Verified
                </Text>
              </View>
              <View style={styles.supplierRating}>
                <IconStar size={12} color={colors.success} />
                <Text style={styles.supplierRatingText}>{s.rating.toFixed(1)}</Text>
              </View>
            </Pressable>
          ))}
        </View>

        {/* recommended */}
        <SectionHeader title="Recommended for You" action="See all" onAction={() => navigation.navigate('ProductList', { title: 'Recommended for You', source: 'recommended' })} />
        <View style={styles.grid}>
          {recommended.map((p) => (
            <ProductCard
              key={p.id}
              product={p}
              width={(SCREEN_W - 40 - 13) / 2}
              wished={!!wishlist[p.id]}
              onPress={() => goProduct(p.id)}
              onAdd={() => addToCart(p.id, 1)}
              onToggleWish={() => toggleWish(p.id)}
            />
          ))}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  header: { paddingHorizontal: 20, paddingTop: 8, paddingBottom: 16 },
  deliverRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 14 },
  deliverLabel: { fontSize: 12, color: colors.textMuted, fontFamily: fonts.bodyMedium },
  deliverValue: { fontSize: 12, color: colors.text, fontFamily: fonts.bodyBold },
  greetRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  greetLeft: { flexDirection: 'row', alignItems: 'center', gap: 11 },
  avatar: {
    width: 44,
    height: 44,
    borderRadius: 13,
    backgroundColor: colors.bgDark,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { color: '#fff', fontFamily: fonts.displayExtra, fontSize: 17 },
  greetSmall: { fontSize: 12.5, color: colors.textMuted, fontFamily: fonts.bodyMedium },
  greetName: { fontSize: 16, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  bellBtn: {
    width: 44,
    height: 44,
    borderRadius: 13,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  searchBar: {
    marginTop: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    height: 50,
    borderWidth: 1,
    borderColor: colors.borderStrong,
    borderRadius: radii.lg,
    paddingHorizontal: 15,
    backgroundColor: colors.bgAlt,
  },
  searchPlaceholder: { flex: 1, fontSize: 14, color: colors.textFaint },
  bannerWrap: { borderRadius: radii.xxl, overflow: 'hidden', height: 150 },
  banner: { height: 150, position: 'relative' },
  bannerOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    padding: 20,
    justifyContent: 'center',
    backgroundColor: 'rgba(15,20,28,0.55)',
  },
  bannerBadge: { fontSize: 10, fontFamily: fonts.bodyExtra, letterSpacing: 1.2, color: colors.accentSoft },
  bannerTitle: { fontSize: 20, fontFamily: fonts.displayExtra, color: '#fff', marginTop: 7, maxWidth: 220 },
  bannerSub: { fontSize: 12, color: '#D1D5DB', marginTop: 6 },
  bannerDots: { position: 'absolute', bottom: 13, left: 22, flexDirection: 'row', gap: 5 },
  bannerDot: { height: 6, width: 6, borderRadius: 4, backgroundColor: 'rgba(255,255,255,0.4)' },
  bannerDotActive: { width: 18, backgroundColor: '#fff' },
  catItem: { alignItems: 'center', gap: 8, width: 58 },
  catIconBox: {
    width: 58,
    height: 58,
    borderRadius: 17,
    backgroundColor: colors.bgAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  catLabel: { fontSize: 11, fontFamily: fonts.bodySemibold, color: colors.textSub },
  brandCard: {
    width: 84,
    height: 74,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.lg,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    ...shadow.card,
  },
  brandIcon: { width: 34, height: 34, borderRadius: 10, backgroundColor: colors.bgDark, alignItems: 'center', justifyContent: 'center' },
  brandIconText: { color: '#fff', fontFamily: fonts.displayExtra, fontSize: 12 },
  brandName: { fontSize: 10, fontFamily: fonts.bodySemibold, color: colors.textMuted },
  dealsCard: {
    marginHorizontal: 20,
    marginTop: 28,
    backgroundColor: colors.bgDark,
    borderRadius: radii.xxl,
    padding: 18,
    paddingBottom: 20,
  },
  dealsHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 },
  dealsTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 9 },
  dealsTitle: { fontSize: 17, fontFamily: fonts.displayExtra, color: '#fff' },
  dealsEnds: { fontSize: 12, fontFamily: fonts.bodyBold, color: colors.accentSoft },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: 20,
    gap: 13,
  },
  supplierRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 13,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 15,
    padding: 13,
    backgroundColor: '#fff',
    ...shadow.card,
  },
  supplierIcon: {
    width: 48,
    height: 48,
    borderRadius: 13,
    backgroundColor: colors.bgAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  supplierNameRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  supplierName: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.text },
  supplierMeta: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
  supplierRating: { flexDirection: 'row', alignItems: 'center', gap: 3, backgroundColor: colors.successBg, paddingHorizontal: 9, paddingVertical: 5, borderRadius: 9 },
  supplierRatingText: { fontSize: 12, fontFamily: fonts.bodyBold, color: colors.successDark },
});
