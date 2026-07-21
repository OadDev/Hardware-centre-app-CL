import React, { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { BackButton, PrimaryButton } from '../components/UI';
import { IconGoogle, IconLock, IconMail } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'Login'>;

const EMAIL_RE = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

export default function LoginScreen({ navigation }: Props) {
  const { requestOtp } = useApp();
  const [email, setEmail] = useState('rajesh@kumarhardware.in');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [remember, setRemember] = useState(true);
  const [sending, setSending] = useState(false);

  const doLogin = async () => {
    const e = email.trim();
    if (!EMAIL_RE.test(e)) {
      setError('Enter a valid email address.');
      return;
    }
    setError('');
    setSending(true);
    const debugOtp = await requestOtp(e);
    setSending(false);
    navigation.navigate('Otp', { email: e, debugOtp });
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.title}>Welcome back</Text>
        <Text style={styles.sub}>Login to your Hardware Centre account.</Text>

        <Text style={styles.label}>Email Address</Text>
        <View style={[styles.inputRow, { borderColor: error ? colors.danger : colors.borderStrong }]}>
          <IconMail />
          <TextInput
            value={email}
            onChangeText={(v) => {
              setEmail(v);
              setError('');
            }}
            placeholder="you@business.in"
            keyboardType="email-address"
            autoCapitalize="none"
            style={styles.input}
            placeholderTextColor={colors.textFaint}
          />
        </View>

        <Text style={[styles.label, { marginTop: 18 }]}>Password</Text>
        <View style={[styles.inputRow, { borderColor: colors.borderStrong }]}>
          <IconLock />
          <TextInput
            value={password}
            onChangeText={setPassword}
            placeholder="••••••••"
            secureTextEntry
            style={styles.input}
            placeholderTextColor={colors.textFaint}
          />
        </View>

        {!!error && <Text style={styles.error}>{error}</Text>}

        <View style={styles.rowBetween}>
          <Pressable style={styles.rememberRow} onPress={() => setRemember(!remember)}>
            <View style={[styles.checkbox, remember ? styles.checkboxOn : null]}>
              {remember && <View style={styles.checkboxDot} />}
            </View>
            <Text style={styles.rememberText}>Remember me</Text>
          </Pressable>
          <Text style={styles.forgot}>Forgot Password?</Text>
        </View>

        <PrimaryButton label="Login" onPress={doLogin} loading={sending} style={{ marginTop: 28 }} />

        <View style={styles.dividerRow}>
          <View style={styles.divider} />
          <Text style={styles.or}>or</Text>
          <View style={styles.divider} />
        </View>

        <Pressable style={styles.googleBtn} onPress={doLogin}>
          <IconGoogle />
          <Text style={styles.googleText}>Continue with Google</Text>
        </Pressable>

        <Text style={styles.createRow}>
          New here?{' '}
          <Text style={styles.createLink} onPress={() => navigation.goBack()}>
            Create Account
          </Text>
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  scroll: { paddingHorizontal: 28, paddingBottom: 36 },
  title: {
    marginTop: 26,
    fontSize: 28,
    fontFamily: fonts.displayExtra,
    color: colors.text,
    letterSpacing: -0.5,
  },
  sub: {
    fontSize: 14.5,
    color: colors.textMuted,
    marginTop: 8,
    marginBottom: 30,
  },
  label: {
    fontSize: 13,
    fontFamily: fonts.bodySemibold,
    color: colors.textSub,
  },
  inputRow: {
    marginTop: 8,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    height: 52,
    borderWidth: 1.5,
    borderRadius: radii.md,
    paddingHorizontal: 14,
    backgroundColor: colors.bgAlt,
  },
  input: {
    flex: 1,
    fontSize: 15,
    color: colors.text,
    padding: 0,
  },
  error: {
    marginTop: 10,
    fontSize: 12.5,
    color: colors.danger,
    fontFamily: fonts.bodyMedium,
  },
  rowBetween: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 18,
  },
  rememberRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkboxOn: {
    backgroundColor: colors.accent,
    borderColor: colors.accent,
  },
  checkboxDot: {
    width: 8,
    height: 8,
    borderRadius: 2,
    backgroundColor: '#fff',
  },
  rememberText: {
    fontSize: 13.5,
    color: colors.textSub,
    fontFamily: fonts.bodyMedium,
  },
  forgot: {
    fontSize: 13.5,
    color: colors.accent,
    fontFamily: fonts.bodySemibold,
  },
  dividerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    marginVertical: 24,
  },
  divider: { flex: 1, height: 1, backgroundColor: colors.borderStrong },
  or: { fontSize: 12.5, color: colors.textFaint, fontFamily: fonts.bodyMedium },
  googleBtn: {
    height: 52,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    borderRadius: radii.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
  },
  googleText: {
    fontSize: 15,
    fontFamily: fonts.bodySemibold,
    color: colors.text,
  },
  createRow: {
    textAlign: 'center',
    marginTop: 26,
    fontSize: 14,
    color: colors.textMuted,
  },
  createLink: {
    color: colors.accent,
    fontFamily: fonts.bodyBold,
  },
});
