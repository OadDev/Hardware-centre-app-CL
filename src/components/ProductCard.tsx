import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Product } from '../api/types';
import { colors, fonts, radii, shadow } from '../theme/theme';
import { discountPct, inr } from '../utils/format';
import { IconHeart, IconPlus, IconStar } from './Icons';
import PlaceholderImage from './PlaceholderImage';

export default function ProductCard({
  product,
  wished,
  onPress,
  onAdd,
  onToggleWish,
  width,
}: {
  product: Product;
  wished: boolean;
  onPress: () => void;
  onAdd: () => void;
  onToggleWish: () => void;
  width?: number;
}) {
  const off = discountPct(product.price, product.mrp);
  const tagBg = product.tag === 'Deal' ? colors.dangerBg : colors.accentLight;
  const tagColor = product.tag === 'Deal' ? colors.danger : colors.accentDark;

  return (
    <Pressable onPress={onPress} style={[styles.card, shadow.card, width ? { width } : null]}>
      <View style={styles.imageWrap}>
        <PlaceholderImage seed={product.id} label={product.brand} style={styles.image} />
        <Pressable onPress={onToggleWish} hitSlop={8} style={styles.wishBtn}>
          <IconHeart size={16} color={wished ? colors.danger : 'none'} stroke={wished ? colors.danger : colors.textFaint} />
        </Pressable>
        {!!product.tag && (
          <View style={[styles.tag, { backgroundColor: tagBg }]}>
            <Text style={[styles.tagText, { color: tagColor }]}>{product.tag}</Text>
          </View>
        )}
      </View>
      <View style={styles.body}>
        <Text style={styles.brand} numberOfLines={1}>
          {product.brand.toUpperCase()}
        </Text>
        <Text style={styles.name} numberOfLines={2}>
          {product.name}
        </Text>
        <View style={styles.ratingRow}>
          <IconStar size={12} />
          <Text style={styles.ratingText}>{product.rating.toFixed(1)}</Text>
          <Text style={styles.reviewsText}>({product.reviews.toLocaleString('en-IN')})</Text>
        </View>
        <View style={styles.spacer} />
        <View style={styles.priceRow}>
          <View style={{ minWidth: 0 }}>
            <Text style={styles.price}>{inr(product.price)}</Text>
            {off > 0 && <Text style={styles.mrp}>{inr(product.mrp)}</Text>}
          </View>
          <Pressable onPress={onAdd} style={styles.addBtn} hitSlop={6}>
            <IconPlus size={17} />
          </Pressable>
        </View>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.bg,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.xl,
    overflow: 'hidden',
  },
  imageWrap: {
    position: 'relative',
  },
  image: {
    width: '100%',
    height: 122,
  },
  wishBtn: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: 'rgba(255,255,255,0.94)',
    alignItems: 'center',
    justifyContent: 'center',
    ...shadow.float,
  },
  tag: {
    position: 'absolute',
    top: 8,
    left: 8,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 7,
  },
  tagText: {
    fontSize: 9.5,
    fontFamily: fonts.bodyBold,
    letterSpacing: 0.3,
    textTransform: 'uppercase',
  },
  body: {
    padding: 11,
    paddingTop: 10,
    flex: 1,
  },
  brand: {
    fontSize: 10,
    fontFamily: fonts.bodyBold,
    color: colors.textFaint,
    letterSpacing: 0.5,
  },
  name: {
    fontSize: 12.5,
    fontFamily: fonts.bodySemibold,
    color: colors.text,
    lineHeight: 17,
    marginTop: 3,
    minHeight: 34,
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 6,
  },
  ratingText: {
    fontSize: 11,
    fontFamily: fonts.bodySemibold,
    color: colors.textSub,
  },
  reviewsText: {
    fontSize: 10.5,
    color: colors.textFaint,
  },
  spacer: { flex: 1 },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    marginTop: 9,
  },
  price: {
    fontSize: 15,
    fontFamily: fonts.displayExtra,
    color: colors.text,
  },
  mrp: {
    fontSize: 10.5,
    color: colors.textFaint,
    textDecorationLine: 'line-through',
  },
  addBtn: {
    width: 33,
    height: 33,
    borderRadius: 9,
    backgroundColor: colors.bgDark,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
