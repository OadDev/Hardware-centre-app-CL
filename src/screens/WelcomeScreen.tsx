import React from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts } from '../theme/theme';
import { PrimaryButton, SecondaryButton } from '../components/UI';

type Props = NativeStackScreenProps<RootStackParamList, 'Welcome'>;

export default function WelcomeScreen({ navigation }: Props) {
  const { enterAsGuest } = useApp();

  return (
    <View style={styles.container}>
      <View style={styles.top}>
        <Image source={require('../../assets/brand/hc-logo.png')} style={styles.logo} />
        <Text style={styles.appName}>Hardware Centre</Text>
        <Text style={styles.tagline}>India's B2B marketplace for verified hardware suppliers.</Text>
      </View>
      <SafeAreaView edges={['bottom']} style={styles.sheet}>
        <Text style={styles.heading}>Get started</Text>
        <Text style={styles.sub}>Login or create your business account.</Text>
        <PrimaryButton label="Login" onPress={() => navigation.navigate('Login')} style={{ marginTop: 22 }} />
        <SecondaryButton label="Create Account" onPress={() => navigation.navigate('Login')} style={{ marginTop: 12 }} />
        <Text
          style={styles.guest}
          onPress={() => {
            enterAsGuest();
            navigation.reset({ index: 0, routes: [{ name: 'Main', params: { screen: 'Home' } }] });
          }}
        >
          Continue as Guest
        </Text>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bgDark,
  },
  top: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 32,
    paddingTop: 60,
    paddingBottom: 40,
  },
  logo: {
    width: 120,
    height: 120,
    borderRadius: 28,
  },
  appName: {
    marginTop: 26,
    fontSize: 26,
    fontFamily: fonts.displayExtra,
    color: '#fff',
    letterSpacing: -0.4,
  },
  tagline: {
    marginTop: 10,
    fontSize: 14,
    color: '#9CA3AF',
    textAlign: 'center',
    lineHeight: 21,
    maxWidth: 260,
  },
  sheet: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    paddingHorizontal: 28,
    paddingTop: 30,
    paddingBottom: 10,
  },
  heading: {
    fontSize: 21,
    fontFamily: fonts.displayExtra,
    color: colors.text,
  },
  sub: {
    fontSize: 13.5,
    color: colors.textMuted,
    marginTop: 5,
  },
  guest: {
    marginTop: 18,
    textAlign: 'center',
    color: colors.textMuted,
    fontSize: 14.5,
    fontFamily: fonts.bodySemibold,
    paddingVertical: 8,
  },
});
