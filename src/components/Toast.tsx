import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, Text, View } from 'react-native';
import { useApp } from '../context/AppContext';
import { IconCheckCircle } from './Icons';
import { colors, fonts } from '../theme/theme';

export default function Toast() {
  const { toast } = useApp();
  const opacity = useRef(new Animated.Value(0)).current;
  const translateY = useRef(new Animated.Value(14)).current;

  useEffect(() => {
    if (toast) {
      opacity.setValue(0);
      translateY.setValue(14);
      Animated.parallel([
        Animated.timing(opacity, { toValue: 1, duration: 220, useNativeDriver: true }),
        Animated.timing(translateY, { toValue: 0, duration: 220, useNativeDriver: true }),
      ]).start();
    }
  }, [toast?.key]);

  if (!toast) return null;

  return (
    <View style={styles.wrap} pointerEvents="none">
      <Animated.View style={[styles.toast, { opacity, transform: [{ translateY }] }]}>
        <IconCheckCircle size={17} color={colors.success} strokeWidth={2.5} />
        <Text style={styles.text}>{toast.message}</Text>
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 100,
    alignItems: 'center',
    zIndex: 999,
  },
  toast: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 9,
    backgroundColor: colors.bgDark,
    paddingHorizontal: 20,
    paddingVertical: 13,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOpacity: 0.3,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 8 },
    elevation: 10,
  },
  text: {
    color: '#fff',
    fontSize: 13.5,
    fontFamily: fonts.bodySemibold,
  },
});
