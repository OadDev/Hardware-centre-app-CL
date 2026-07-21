import React from 'react';
import { StyleProp, StyleSheet, Text, View, ViewStyle } from 'react-native';
import { fonts } from '../theme/theme';

const PALETTE = [
  ['#1F2937', '#374151'],
  ['#7C2D12', '#9A3412'],
  ['#0F766E', '#115E59'],
  ['#1E3A8A', '#1D4ED8'],
  ['#4C1D95', '#5B21B6'],
  ['#78350F', '#92400E'],
];

function hashStr(s: string): number {
  let h = 0;
  for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
  return h;
}

export default function PlaceholderImage({
  seed,
  label,
  style,
  radius = 0,
}: {
  seed: string;
  label?: string;
  style?: StyleProp<ViewStyle>;
  radius?: number;
}) {
  const idx = hashStr(seed) % PALETTE.length;
  const [c1] = PALETTE[idx];
  const text = (label || seed).trim();
  const shortLabel = text
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0])
    .join('')
    .toUpperCase();

  return (
    <View style={[styles.box, { backgroundColor: c1, borderRadius: radius }, style]}>
      <Text style={styles.label}>{shortLabel}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  box: {
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  label: {
    color: 'rgba(255,255,255,0.5)',
    fontSize: 22,
    fontFamily: fonts.displayExtra,
    letterSpacing: 1,
  },
});
