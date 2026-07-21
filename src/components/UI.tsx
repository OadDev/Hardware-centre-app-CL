import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View, ViewStyle } from 'react-native';
import { colors, fonts, radii } from '../theme/theme';
import { IconChevronLeft } from './Icons';

export function PrimaryButton({
  label,
  onPress,
  style,
  disabled,
  loading,
}: {
  label: string;
  onPress: () => void;
  style?: ViewStyle;
  disabled?: boolean;
  loading?: boolean;
}) {
  return (
    <Pressable
      onPress={onPress}
      disabled={disabled || loading}
      style={({ pressed }) => [
        styles.primary,
        disabled ? styles.primaryDisabled : null,
        pressed && !disabled ? styles.pressed : null,
        style,
      ]}
    >
      {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.primaryText}>{label}</Text>}
    </Pressable>
  );
}

export function SecondaryButton({ label, onPress, style }: { label: string; onPress: () => void; style?: ViewStyle }) {
  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.secondary, pressed ? styles.pressed : null, style]}>
      <Text style={styles.secondaryText}>{label}</Text>
    </Pressable>
  );
}

export function BackButton({ onPress, dark }: { onPress: () => void; dark?: boolean }) {
  return (
    <Pressable onPress={onPress} style={styles.backBtn} hitSlop={8}>
      <IconChevronLeft color={dark ? '#fff' : colors.text} />
    </Pressable>
  );
}

export function SectionHeader({ title, action, onAction }: { title: string; action?: string; onAction?: () => void }) {
  return (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {action ? (
        <Pressable onPress={onAction} hitSlop={6}>
          <Text style={styles.sectionAction}>{action}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  primary: {
    height: 54,
    borderRadius: radii.md,
    backgroundColor: colors.accent,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: colors.accent,
    shadowOpacity: 0.28,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 8 },
    elevation: 4,
  },
  primaryDisabled: {
    backgroundColor: '#FBBF77',
    shadowOpacity: 0,
    elevation: 0,
  },
  primaryText: {
    color: '#fff',
    fontSize: 16,
    fontFamily: fonts.bodyBold,
  },
  secondary: {
    height: 54,
    borderRadius: radii.md,
    borderWidth: 1.5,
    borderColor: colors.bgDark,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryText: {
    color: colors.bgDark,
    fontSize: 16,
    fontFamily: fonts.bodyBold,
  },
  pressed: {
    opacity: 0.85,
  },
  backBtn: {
    width: 42,
    height: 42,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.borderStrong,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginTop: 28,
    marginBottom: 14,
  },
  sectionTitle: {
    fontSize: 18,
    fontFamily: fonts.displayExtra,
    color: colors.text,
    letterSpacing: -0.3,
  },
  sectionAction: {
    fontSize: 13,
    fontFamily: fonts.bodyBold,
    color: colors.accent,
  },
});
