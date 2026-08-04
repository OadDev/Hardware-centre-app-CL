import React, { useState } from 'react';
import { ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useFocusEffect } from '@react-navigation/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { BackButton, PrimaryButton } from '../components/UI';
import { IconMail, IconPhone } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'Login'>;

const EMAIL_RE = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
const PHONE_RE = /^[6-9]\d{9}$/;

// There is no separate password-based account system and no distinct sign-up
// form — logging in and creating an account are the same action here: the
// first time a phone/email verifies its OTP, an account is created for it
// automatically. This screen only ever adapts its wording based on which
// button was tapped on the Welcome screen; the underlying flow is identical.
export default function LoginScreen({ navigation, route }: Props) {
  const { requestOtp, otpChannel, refreshOtpChannel } = useApp();
  const isSms = otpChannel === 'sms';
  const [mode, setMode] = useState<'login' | 'signup'>(route.params?.mode ?? 'login');
  const isSignup = mode === 'signup';

  // Re-check which OTP channel the admin has active every time this screen
  // is shown, not just once at app launch — otherwise a channel change made
  // in Admin > Settings while the app is already open goes unnoticed until
  // the app is fully restarted.
  useFocusEffect(
    React.useCallback(() => {
      refreshOtpChannel();
    }, [refreshOtpChannel])
  );

  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [error, setError] = useState('');
  const [sending, setSending] = useState(false);

  const doContinue = async () => {
    const contact = isSms ? phone.trim() : email.trim();
    if (isSms ? !PHONE_RE.test(contact) : !EMAIL_RE.test(contact)) {
      setError(isSms ? 'Enter a valid 10-digit mobile number.' : 'Enter a valid email address.');
      return;
    }
    setError('');
    setSending(true);
    const { ok, debugOtp } = await requestOtp(contact);
    setSending(false);
    if (ok) {
      navigation.navigate('Otp', { contact, channel: otpChannel, debugOtp });
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.title}>{isSignup ? 'Create your account' : 'Welcome back'}</Text>
        <Text style={styles.sub}>
          {isSignup
            ? "We'll text or email you a verification code — no password needed."
            : 'Enter your email or phone to get a verification code.'}
        </Text>

        <Text style={styles.label}>{isSms ? 'Mobile Number' : 'Email Address'}</Text>
        <View style={[styles.inputRow, { borderColor: error ? colors.danger : colors.borderStrong }]}>
          {isSms ? <IconPhone /> : <IconMail />}
          {isSms ? (
            <TextInput
              value={phone}
              onChangeText={(v) => {
                setPhone(v.replace(/\D/g, '').slice(0, 10));
                setError('');
              }}
              placeholder="98765 43210"
              keyboardType="phone-pad"
              style={styles.input}
              placeholderTextColor={colors.textFaint}
              autoFocus
            />
          ) : (
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
              autoFocus
            />
          )}
        </View>

        {!!error && <Text style={styles.error}>{error}</Text>}

        <PrimaryButton label="Send Verification Code" onPress={doContinue} loading={sending} style={{ marginTop: 24 }} />

        <Text style={styles.switchRow}>
          {isSignup ? 'Already have an account? ' : 'New here? '}
          <Text style={styles.switchLink} onPress={() => setMode(isSignup ? 'login' : 'signup')}>
            {isSignup ? 'Login' : 'Create Account'}
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
    lineHeight: 21,
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
  switchRow: {
    textAlign: 'center',
    marginTop: 26,
    fontSize: 14,
    color: colors.textMuted,
  },
  switchLink: {
    color: colors.accent,
    fontFamily: fonts.bodyBold,
  },
});
