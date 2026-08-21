import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Image, Linking, Pressable, ScrollView, Share, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { ProductApi } from '../api/endpoints';
import { ProductDetail, ProductPriceTier } from '../api/types';
import { discountPct, inr } from '../utils/format';
import PlaceholderImage from '../components/PlaceholderImage';
import {
  IconCheckCircle,
  IconChevronLeft,
  IconHeart,
  IconMinus,
  IconPhone,
  IconPlay,
  IconPlus,
  IconShare,
  IconStar,
  IconTruck,
  IconVerified,
  IconWhatsapp,
} from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'ProductDetail'>;

function priceForQuantity(basePrice: number, tiers: ProductPriceTier[], quantity: number): number {
  const applicable = tiers.filter((t) => t.min_qty <= quantity).sort((a, b) => b.min_qty - a.min_qty)[0];
  return applicable ? applicable.price : basePrice;
}

export default function ProductDetailScreen({ navigation, route }: Props) {
  const { id } = route.params;
  const { wishlist, toggleWish, addToCart, cart, deliveryLabel } = useApp();
  const insets = useSafeAreaInsets();
  const [qty, setQty] = useState(1);
  const [gallery, setGallery] = useState(0);
  const [detail, setDetail] = useState<ProductDetail | null>(null);
  const [selectedVariantId, setSelectedVariantId] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    setDetail(null);
    setGallery(0);
    setQty(1);
    setSelectedVariantId(null);
    ProductApi.show(id).then((res) => {
      if (cancelled) return;
      setDetail(res);
    });
    return () => {
      cancelled = true;
    };
  }, [id]);

  const back = () => (navigation.canGoBack() ? navigation.goBack() : navigation.navigate('Main', { screen: 'Home' }));

  const shareProduct = () => {
    if (!detail) return;
    Share.share({
      message: `${detail.product.name} — ${inr(detail.product.price)} on Hardware Centers`,
    }).catch(() => {});
  };

  if (!detail) {
    return (
      <View style={[styles.container, { alignItems: 'center', justifyContent: 'center' }]}>
        <ActivityIndicator color={colors.accent} />
      </View>
    );
  }

  const { product, reviews, rating_breakdown: ratingBars } = detail;
  const variants = product.variants ?? [];
  const selectedVariant = variants.find((v) => v.id === selectedVariantId);

  // A variant with its own bulk-pricing tiers uses those; otherwise it only
  // inherits the base product's tiers when the seller opted every variant
  // into them via apply_bulk_pricing_to_all_variants.
  const activeTiers: ProductPriceTier[] = variants.length > 0
    ? (selectedVariant
      ? (selectedVariant.price_tiers.length ? selectedVariant.price_tiers : (product.apply_bulk_pricing_to_all_variants ? product.price_tiers : []))
      : [])
    : (product.price_tiers ?? []);
  const basePrice = selectedVariant ? selectedVariant.price : product.price;
  const effectivePrice = variants.length > 0 && !selectedVariant ? product.price : priceForQuantity(basePrice, activeTiers, qty);
  const off = discountPct(effectivePrice, product.mrp);
  const wished = !!wishlist[product.id];
  const images = product.images ?? [];
  const heroImage = selectedVariant?.image ?? images[gallery] ?? images[0];
  const availableStock = variants.length > 0 ? (selectedVariant?.stock ?? 0) : product.stock;
  const outOfStock = variants.length > 0 ? (!selectedVariant || selectedVariant.stock <= 0) : product.stock <= 0;
  const exceedsStock = qty > availableStock;
  const inCart = cart.items.some(
    (i) => i.product.id === product.id && (i.variant?.id ?? undefined) === selectedVariant?.id
  );
  const stockLabel = variants.length > 0
    ? (selectedVariant ? (selectedVariant.stock > 0 ? `${selectedVariant.stock} in stock` : 'Out of Stock') : 'Select an option')
    : product.stock_status;

  const contactDigits = detail.contact_number?.replace(/\D/g, '') ?? '';
  const waNumber = contactDigits.length === 10 ? `91${contactDigits}` : contactDigits;
  const waMessage = encodeURIComponent(`Hi, I am interested in ${product.name}`);

  return (
    <View style={styles.container}>
      <ScrollView contentContainerStyle={{ paddingBottom: 110 }} showsVerticalScrollIndicator={false}>
        <View style={styles.imageSection}>
          {heroImage ? (
            <Image source={{ uri: heroImage }} style={styles.heroImage} resizeMode="cover" />
          ) : (
            <PlaceholderImage seed={product.id} label={product.name} style={styles.heroImage} />
          )}
          <View style={[styles.imageTopBar, { top: insets.top + 14 }]}>
            <Pressable onPress={back} style={styles.roundBtn}>
              <IconChevronLeft />
            </Pressable>
            <View style={{ flexDirection: 'row', gap: 9 }}>
              <Pressable style={styles.roundBtn} onPress={shareProduct}>
                <IconShare />
              </Pressable>
              <Pressable style={styles.roundBtn} onPress={() => toggleWish(product.id)}>
                <IconHeart size={19} color={wished ? colors.danger : colors.textFaint} stroke={wished ? colors.danger : colors.textFaint} strokeWidth={2} />
              </Pressable>
            </View>
          </View>
          {off > 0 && (
            <View style={styles.offBadge}>
              <Text style={styles.offBadgeText}>-{off}% OFF</Text>
            </View>
          )}
          {!!product.video_url && (
            <Pressable
              style={styles.videoBadge}
              onPress={() => Linking.openURL(product.video_url as string)}
            >
              <IconPlay size={13} />
              <Text style={styles.videoBadgeText}>Watch Video</Text>
            </Pressable>
          )}
        </View>

        {images.length > 0 ? (
          <View style={styles.galleryRow}>
            {images.map((uri, i) => (
              <Pressable key={uri} onPress={() => setGallery(i)}>
                <Image
                  source={{ uri }}
                  style={[styles.galleryThumb, { borderColor: i === gallery ? colors.accent : colors.borderStrong }]}
                  resizeMode="cover"
                />
              </Pressable>
            ))}
          </View>
        ) : (
          <View style={styles.galleryRow}>
            {[0, 1, 2, 3].map((i) => (
              <PlaceholderImage
                key={i}
                seed={product.id + '-g' + i}
                label=" "
                radius={11}
                style={[styles.galleryThumb, { borderColor: colors.borderStrong }]}
              />
            ))}
          </View>
        )}

        <View style={styles.section}>
          <View style={styles.rowBetween}>
            <Text style={styles.brand}>{product.brand.toUpperCase()}</Text>
            <View style={styles.ratingPill}>
              <IconStar size={13} color={colors.success} />
              <Text style={styles.ratingPillText}>{product.rating.toFixed(1)}</Text>
              <Text style={styles.ratingPillSub}>({product.reviews.toLocaleString('en-IN')})</Text>
            </View>
          </View>
          <Text style={styles.name}>{product.name}</Text>
          <View style={styles.priceRow}>
            <Text style={styles.price}>{inr(effectivePrice)}</Text>
            <Text style={styles.mrp}>{inr(product.mrp)}</Text>
            {off > 0 && <Text style={styles.offText}>-{off}%</Text>}
          </View>
          <Text style={styles.taxNote}>Inclusive of all taxes · per {product.unit}</Text>

          {activeTiers.length > 0 && (
            <View style={styles.tierBox}>
              <Text style={styles.tierTitle}>Buy more, save more</Text>
              {[{ min_qty: 1, price: basePrice }, ...activeTiers]
                .sort((a, b) => a.min_qty - b.min_qty)
                .map((tier, i, arr) => {
                  const isActiveRow = qty >= tier.min_qty && (!arr[i + 1] || qty < arr[i + 1].min_qty);
                  return (
                    <View key={tier.min_qty} style={[styles.tierRow, i > 0 ? styles.tierRowBorder : null, isActiveRow ? styles.tierRowActive : null]}>
                      <Text style={styles.tierRange}>
                        {arr[i + 1] ? `${tier.min_qty} – ${arr[i + 1].min_qty - 1}` : `${tier.min_qty}+`} {product.unit}
                      </Text>
                      <Text style={styles.tierPrice}>{inr(tier.price)} / {product.unit}</Text>
                    </View>
                  );
                })}
            </View>
          )}

          {!!waNumber && (
            <View style={styles.contactRow}>
              <Pressable style={styles.contactBtn} onPress={() => Linking.openURL(`tel:+${waNumber}`)}>
                <IconPhone size={17} color={colors.accentDark} />
                <Text style={styles.contactBtnText}>Buy on Call</Text>
              </Pressable>
              <Pressable style={styles.contactBtn} onPress={() => Linking.openURL(`https://wa.me/${waNumber}?text=${waMessage}`)}>
                <IconWhatsapp size={17} />
                <Text style={styles.contactBtnText}>Buy on Chat</Text>
              </Pressable>
            </View>
          )}

          {variants.length > 0 && (
            <View style={styles.optionGroup}>
              <Text style={styles.optionLabel}>Options</Text>
              <View style={styles.optionRow}>
                {variants.map((v) => {
                  const active = v.id === selectedVariantId;
                  return (
                    <Pressable
                      key={v.id}
                      onPress={() => setSelectedVariantId(v.id)}
                      style={[styles.optionChip, active ? styles.optionChipActive : null]}
                    >
                      <Text style={[styles.optionChipText, active ? styles.optionChipTextActive : null]}>{v.label}</Text>
                    </Pressable>
                  );
                })}
              </View>
            </View>
          )}

          <View style={styles.infoCards}>
            <View style={[styles.infoCard, { backgroundColor: outOfStock ? colors.dangerBg : colors.successBg }]}>
              <IconCheckCircle color={outOfStock ? colors.danger : undefined} />
              <View>
                <Text style={styles.infoCardTitle}>{stockLabel}</Text>
                <Text style={styles.infoCardSub}>{outOfStock ? 'Currently unavailable' : 'Ready to ship'}</Text>
              </View>
            </View>
            <View style={[styles.infoCard, { backgroundColor: colors.bgAlt, borderWidth: 1, borderColor: colors.border }]}>
              <IconTruck />
              <View>
                <Text style={styles.infoCardTitle}>Delivery in {product.eta}–{product.eta + 1} days</Text>
                <Text style={styles.infoCardSub}>{deliveryLabel ? `to ${deliveryLabel}` : 'set your location for exact delivery'}</Text>
              </View>
            </View>
          </View>
        </View>

        <View style={styles.divider} />

        <View style={styles.section}>
          <Text style={styles.h2}>Specifications</Text>
          <View style={styles.specBox}>
            {product.specs.map(([label, val], i) => (
              <View key={label} style={[styles.specRow, i === product.specs.length - 1 ? { borderBottomWidth: 0 } : null]}>
                <Text style={styles.specLabel}>{label}</Text>
                <Text style={styles.specVal}>{val}</Text>
              </View>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.h2}>Description</Text>
          <Text style={styles.desc}>{product.desc}</Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.h2}>Sold by</Text>
          <Pressable
            style={styles.supplierCard}
            onPress={() => navigation.navigate('ProductList', { title: product.supplier, supplier: product.supplier })}
          >
            <View style={styles.supplierIcon}>
              <IconVerified size={22} color={colors.bgDark} />
            </View>
            <View style={{ flex: 1 }}>
              <View style={styles.supplierNameRow}>
                <Text style={styles.supplierName}>{product.supplier}</Text>
                <IconVerified />
              </View>
              <Text style={styles.supplierMeta}>
                {product.loc} · ⭐ {product.seller_rating.toFixed(1)} · {product.seller_years} yrs in business
              </Text>
            </View>
          </Pressable>
        </View>

        <View style={[styles.section, { paddingBottom: 8 }]}>
          <Text style={styles.h2}>Ratings &amp; Reviews</Text>
          <View style={styles.ratingSummaryRow}>
            <View style={styles.ratingBig}>
              <Text style={styles.ratingBigNum}>{product.rating.toFixed(1)}</Text>
              <Text style={styles.ratingBigStars}>★★★★★</Text>
              <Text style={styles.ratingBigCount}>{product.reviews.toLocaleString('en-IN')} reviews</Text>
            </View>
            <View style={{ flex: 1, gap: 5 }}>
              {ratingBars.map((b) => (
                <View key={b.stars} style={styles.barRow}>
                  <Text style={styles.barLabel}>{b.stars}</Text>
                  <View style={styles.barTrack}>
                    <View style={[styles.barFill, { width: `${b.pct}%` }]} />
                  </View>
                </View>
              ))}
            </View>
          </View>
          {reviews.map((r) => (
            <View key={r.name} style={styles.reviewRow}>
              <View style={styles.reviewHeader}>
                <View style={styles.reviewAvatar}>
                  <Text style={styles.reviewAvatarText}>{r.init}</Text>
                </View>
                <View>
                  <Text style={styles.reviewName}>{r.name}</Text>
                  <Text style={styles.reviewMeta}>★★★★★ · {r.date}</Text>
                </View>
              </View>
              <Text style={styles.reviewText}>{r.text}</Text>
            </View>
          ))}
        </View>
      </ScrollView>

      <SafeAreaView edges={['bottom']} style={styles.stickyBar}>
        <View style={styles.qtyStepper}>
          <Pressable style={styles.qtyBtn} onPress={() => setQty((q) => Math.max(1, q - 1))}>
            <IconMinus size={18} />
          </Pressable>
          <Text style={styles.qtyText}>{qty}</Text>
          <Pressable
            style={styles.qtyBtn}
            onPress={() => setQty((q) => (availableStock > 0 ? Math.min(availableStock, q + 1) : q + 1))}
          >
            <IconPlus size={17} color={colors.textSub} />
          </Pressable>
        </View>
        <Pressable
          style={[styles.addCartBtn, (outOfStock || exceedsStock) ? styles.btnDisabled : null]}
          disabled={outOfStock || exceedsStock}
          onPress={() =>
            inCart ? navigation.navigate('Main', { screen: 'Cart' }) : addToCart(product.id, qty, selectedVariant?.id)
          }
        >
          <Text style={styles.addCartText}>{inCart ? 'Go to Cart' : 'Add to Cart'}</Text>
        </Pressable>
        <Pressable
          style={[styles.buyNowBtn, (outOfStock || exceedsStock) ? styles.btnDisabled : null]}
          disabled={outOfStock || exceedsStock}
          onPress={() => {
            addToCart(product.id, qty, selectedVariant?.id);
            navigation.navigate('Main', { screen: 'Cart' });
          }}
        >
          <Text style={styles.buyNowText}>Buy Now</Text>
        </Pressable>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  imageSection: { backgroundColor: colors.bgAlt, position: 'relative' },
  heroImage: { width: '100%', height: 320 },
  imageTopBar: {
    position: 'absolute',
    top: 14,
    left: 16,
    right: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  roundBtn: {
    width: 42,
    height: 42,
    borderRadius: 13,
    backgroundColor: 'rgba(255,255,255,0.95)',
    alignItems: 'center',
    justifyContent: 'center',
    ...shadow.float,
  },
  offBadge: {
    position: 'absolute',
    bottom: 14,
    left: 16,
    paddingHorizontal: 11,
    paddingVertical: 6,
    borderRadius: 9,
    backgroundColor: colors.accent,
  },
  offBadgeText: { color: '#fff', fontSize: 12, fontFamily: fonts.bodyExtra },
  videoBadge: {
    position: 'absolute',
    bottom: 14,
    right: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 11,
    paddingVertical: 6,
    borderRadius: 9,
    backgroundColor: 'rgba(17,24,39,0.8)',
  },
  videoBadgeText: { color: '#fff', fontSize: 12, fontFamily: fonts.bodyExtra },
  galleryRow: { flexDirection: 'row', gap: 10, paddingHorizontal: 20, paddingTop: 14 },
  galleryThumb: { width: 60, height: 60, borderWidth: 2 },
  section: { paddingHorizontal: 20, paddingTop: 18 },
  rowBetween: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  brand: { fontSize: 11, fontFamily: fonts.bodyBold, color: colors.accentDark, letterSpacing: 0.6 },
  ratingPill: { flexDirection: 'row', alignItems: 'center', gap: 5, backgroundColor: colors.successBg, paddingHorizontal: 9, paddingVertical: 4, borderRadius: 8 },
  ratingPillText: { fontSize: 12, fontFamily: fonts.bodyBold, color: colors.successDark },
  ratingPillSub: { fontSize: 11, color: colors.textMuted },
  name: { fontSize: 21, fontFamily: fonts.displayExtra, color: colors.text, lineHeight: 26, letterSpacing: -0.3, marginTop: 8 },
  priceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 10, marginTop: 14 },
  price: { fontSize: 27, fontFamily: fonts.displayExtra, color: colors.text },
  mrp: { fontSize: 15, color: colors.textFaint, textDecorationLine: 'line-through' },
  offText: { fontSize: 13, color: colors.successDark, fontFamily: fonts.bodyBold },
  taxNote: { fontSize: 12, color: colors.textMuted, marginTop: 3 },
  tierBox: { marginTop: 14, borderWidth: 1, borderColor: colors.border, borderRadius: radii.md, overflow: 'hidden' },
  tierTitle: { fontSize: 12.5, fontFamily: fonts.bodyBold, color: colors.text, paddingHorizontal: 14, paddingVertical: 10, backgroundColor: colors.bgAlt },
  tierRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 14, paddingVertical: 9 },
  tierRowBorder: { borderTopWidth: 1, borderTopColor: colors.border },
  tierRowActive: { backgroundColor: colors.accentLight },
  tierRange: { fontSize: 13, color: colors.textMuted },
  tierPrice: { fontSize: 13, fontFamily: fonts.bodyBold, color: colors.text },
  contactRow: { flexDirection: 'row', gap: 10, marginTop: 14 },
  contactBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 11, borderRadius: 12, borderWidth: 1.5, borderColor: colors.border },
  contactBtnText: { fontSize: 13.5, fontFamily: fonts.bodyBold, color: colors.text },
  optionGroup: { marginTop: 16 },
  optionLabel: { fontSize: 12.5, fontFamily: fonts.bodyBold, color: colors.textSub, marginBottom: 8 },
  optionRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  optionChip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: radii.md,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
  },
  optionChipActive: { borderColor: colors.accent, backgroundColor: colors.accentLight },
  optionChipText: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.textSub },
  optionChipTextActive: { color: colors.accentDark },
  btnDisabled: { opacity: 0.45 },
  infoCards: { flexDirection: 'row', gap: 10, marginTop: 16 },
  infoCard: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 8, borderRadius: 12, padding: 11 },
  infoCardTitle: { fontSize: 12.5, fontFamily: fonts.bodyBold, color: colors.text },
  infoCardSub: { fontSize: 10.5, color: colors.textMuted },
  divider: { height: 8, backgroundColor: colors.bgAlt, marginTop: 22 },
  h2: { fontSize: 16, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 12 },
  specBox: { borderWidth: 1, borderColor: colors.border, borderRadius: radii.lg, overflow: 'hidden' },
  specRow: { flexDirection: 'row', padding: 13, paddingHorizontal: 15, borderBottomWidth: 1, borderBottomColor: colors.divider },
  specLabel: { flex: 0.42, fontSize: 13, color: colors.textMuted },
  specVal: { flex: 1, fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.text },
  desc: { fontSize: 13.5, color: '#4B5563', lineHeight: 21 },
  supplierCard: { flexDirection: 'row', alignItems: 'center', gap: 13, borderWidth: 1, borderColor: colors.border, borderRadius: 15, padding: 14, backgroundColor: colors.bgAlt },
  supplierIcon: { width: 48, height: 48, borderRadius: 13, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, alignItems: 'center', justifyContent: 'center' },
  supplierNameRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  supplierName: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.text },
  supplierMeta: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
  ratingSummaryRow: { flexDirection: 'row', alignItems: 'center', gap: 16, marginTop: 16, marginBottom: 6 },
  ratingBig: { alignItems: 'center' },
  ratingBigNum: { fontSize: 34, fontFamily: fonts.displayExtra, color: colors.text, lineHeight: 36 },
  ratingBigStars: { fontSize: 11, color: colors.star, marginTop: 5 },
  ratingBigCount: { fontSize: 11, color: colors.textFaint, marginTop: 4 },
  barRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  barLabel: { fontSize: 10, color: colors.textFaint, width: 8 },
  barTrack: { flex: 1, height: 6, borderRadius: 3, backgroundColor: colors.divider, overflow: 'hidden' },
  barFill: { height: '100%', backgroundColor: colors.star },
  reviewRow: { borderTopWidth: 1, borderTopColor: colors.divider, paddingVertical: 14 },
  reviewHeader: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  reviewAvatar: { width: 34, height: 34, borderRadius: 17, backgroundColor: colors.bgDark, alignItems: 'center', justifyContent: 'center' },
  reviewAvatarText: { color: '#fff', fontSize: 14, fontFamily: fonts.displayExtra },
  reviewName: { fontSize: 13, fontFamily: fonts.bodyBold, color: colors.text },
  reviewMeta: { fontSize: 10.5, color: colors.textFaint },
  reviewText: { fontSize: 13, color: '#4B5563', lineHeight: 20, marginTop: 9 },
  stickyBar: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: colors.border,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 12,
    ...shadow.sticky,
  },
  qtyStepper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1.5, borderColor: colors.borderStrong, borderRadius: 12, height: 50 },
  qtyBtn: { width: 42, height: 50, alignItems: 'center', justifyContent: 'center' },
  qtyText: { width: 28, textAlign: 'center', fontSize: 16, fontFamily: fonts.bodyBold, color: colors.text },
  addCartBtn: { flex: 1, height: 50, borderWidth: 1.5, borderColor: colors.bgDark, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
  addCartText: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.bgDark },
  buyNowBtn: { flex: 1, height: 50, borderRadius: 12, backgroundColor: colors.accent, alignItems: 'center', justifyContent: 'center' },
  buyNowText: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: '#fff' },
});
