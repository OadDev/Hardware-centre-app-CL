import React, { useEffect, useRef } from 'react';
import { Animated, Image, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts } from '../theme/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Splash'>;

export default function SplashScreen({ navigation }: Props) {
  const { ready, hasOnboarded, session } = useApp();
  const scale = useRef(new Animated.Value(0.82)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const barWidth = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(scale, { toValue: 1, duration: 700, useNativeDriver: true }),
      Animated.timing(opacity, { toValue: 1, duration: 500, useNativeDriver: true }),
      Animated.timing(barWidth, { toValue: 1, duration: 1600, useNativeDriver: false }),
    ]).start();
  }, []);

  useEffect(() => {
    if (!ready) return;
    const timer = setTimeout(() => {
      if (session) {
        navigation.replace('Main', { screen: 'Home' });
      } else if (hasOnboarded) {
        navigation.replace('Welcome');
      } else {
        navigation.replace('Onboarding');
      }
    }, 1700);
    return () => clearTimeout(timer);
  }, [ready, hasOnboarded, session]);

  return (
    <View style={styles.container}>
      <Animated.View style={{ opacity, transform: [{ scale }] }}>
        <Image source={require('../../assets/brand/hc-logo.png')} style={styles.logo} />
      </Animated.View>
      <Animated.View style={{ opacity, marginTop: 30 }}>
        <Text style={styles.tagline}>
          <Text style={styles.taglineBold}>Everything Hardware.</Text>{' '}
          <Text style={[styles.taglineBold, { color: colors.accent }]}>One Marketplace.</Text>
        </Text>
      </Animated.View>
      <View style={styles.footer}>
        <View style={styles.barTrack}>
          <Animated.View
            style={[
              styles.barFill,
              {
                width: barWidth.interpolate({ inputRange: [0, 1], outputRange: ['0%', '100%'] }),
              },
            ]}
          />
        </View>
        <Text style={styles.brandLabel}>HARDWARE CENTERS</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bgDark,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 148,
    height: 148,
    borderRadius: 32,
  },
  tagline: {
    fontSize: 15,
    letterSpacing: 0.3,
    color: '#F8FAFC',
  },
  taglineBold: {
    fontFamily: fonts.bodySemibold,
  },
  footer: {
    position: 'absolute',
    bottom: 64,
    left: 0,
    right: 0,
    alignItems: 'center',
    gap: 18,
  },
  barTrack: {
    width: 140,
    height: 3,
    borderRadius: 3,
    backgroundColor: 'rgba(255,255,255,0.16)',
    overflow: 'hidden',
  },
  barFill: {
    height: '100%',
    backgroundColor: colors.accent,
    borderRadius: 3,
  },
  brandLabel: {
    color: '#6B7280',
    fontSize: 11,
    letterSpacing: 1.5,
    fontFamily: fonts.bodySemibold,
  },
});
