import React, { useEffect, useRef, useState } from 'react';
import { Animated, StyleSheet, Text, TextInput, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { BackButton, PrimaryButton } from '../components/UI';
import { IconCheckCircle, IconMail, IconPhone } from '../components/Icons';

type Props = NativeStackScreenProps<RootStackParamList, 'Otp'>;

function maskEmail(email: string): string {
  const at = email.indexOf('@');
  if (at > 2) return email[0] + '•••' + email.slice(at - 1);
  return email;
}

function maskPhone(phone: string): string {
  if (phone.length <= 4) return phone;
  return '••••••' + phone.slice(-4);
}

export default function OtpScreen({ navigation, route }: Props) {
  const { contact, channel, debugOtp } = route.params;
  const isSms = channel === 'sms';
  const { requestOtp, verifyOtp, showToast } = useApp();
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [resend, setResend] = useState(60);
  const [success, setSuccess] = useState(false);
  const [verifying, setVerifying] = useState(false);
  const inputs = useRef<Array<TextInput | null>>([]);
  const successAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (debugOtp) showToast(`Dev OTP: ${debugOtp}`);
  }, []);

  useEffect(() => {
    if (resend <= 0) return;
    const t = setInterval(() => setResend((r) => (r <= 1 ? 0 : r - 1)), 1000);
    return () => clearInterval(t);
  }, [resend > 0]);

  const setDigit = (i: number, v: string) => {
    const digit = v.replace(/\D/g, '').slice(-1);
    const next = [...otp];
    next[i] = digit;
    setOtp(next);
    if (digit && i < 5) inputs.current[i + 1]?.focus();
  };

  const onKeyPress = (i: number, key: string) => {
    if (key === 'Backspace' && !otp[i] && i > 0) inputs.current[i - 1]?.focus();
  };

  const resendOtp = async () => {
    setOtp(['', '', '', '', '', '']);
    setResend(60);
    const newDebugOtp = await requestOtp(contact);
    showToast(newDebugOtp ? `Dev OTP: ${newDebugOtp}` : `Code resent via ${isSms ? 'SMS' : 'email'}`);
  };

  const verify = async () => {
    if (otp.some((d) => !d)) {
      showToast('Enter the 6-digit code');
      return;
    }
    setVerifying(true);
    const ok = await verifyOtp(contact, otp.join(''));
    setVerifying(false);
    if (!ok) return;

    setSuccess(true);
    Animated.spring(successAnim, { toValue: 1, useNativeDriver: true, friction: 5 }).start();
    setTimeout(() => {
      navigation.reset({ index: 0, routes: [{ name: 'Main', params: { screen: 'Home' } }] });
    }, 1300);
  };

  const otpDone = otp.every((d) => d);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <View style={{ paddingHorizontal: 28, flex: 1 }}>
        <BackButton onPress={() => navigation.goBack()} />
        <View style={styles.iconWrap}>
          {isSms ? (
            <IconPhone size={30} color={colors.accent} strokeWidth={1.8} />
          ) : (
            <IconMail size={30} color={colors.accent} strokeWidth={1.8} />
          )}
        </View>
        <Text style={styles.title}>{isSms ? 'Verify your number' : 'Verify your email'}</Text>
        <Text style={styles.sub}>
          We've sent a 6-digit verification code to{' '}
          <Text style={styles.bold}>{isSms ? maskPhone(contact) : maskEmail(contact)}</Text>
        </Text>

        <View style={styles.otpRow}>
          {otp.map((v, i) => (
            <TextInput
              key={i}
              ref={(r) => {
                inputs.current[i] = r;
              }}
              value={v}
              onChangeText={(t) => setDigit(i, t)}
              onKeyPress={(e) => onKeyPress(i, e.nativeEvent.key)}
              keyboardType="number-pad"
              maxLength={1}
              style={[styles.otpBox, { borderColor: v ? colors.accent : colors.borderStrong }]}
            />
          ))}
        </View>

        <View style={styles.resendWrap}>
          {resend === 0 ? (
            <Text style={styles.resendLink} onPress={resendOtp}>
              Resend OTP
            </Text>
          ) : (
            <Text style={styles.resendText}>
              Resend code in <Text style={styles.bold}>0:{String(resend).padStart(2, '0')}</Text>
            </Text>
          )}
        </View>

        <PrimaryButton
          label={isSms ? 'Verify Number' : 'Verify Email'}
          onPress={verify}
          disabled={!otpDone}
          loading={verifying}
          style={{ marginTop: 24 }}
        />
        <Text style={styles.changeEmail} onPress={() => navigation.goBack()}>
          {isSms ? 'Change Number' : 'Change Email'}
        </Text>
      </View>

      {success && (
        <Animated.View style={[styles.successOverlay, { opacity: successAnim }]}>
          <Animated.View
            style={[
              styles.successCircle,
              {
                transform: [
                  {
                    scale: successAnim.interpolate({ inputRange: [0, 1], outputRange: [0.4, 1] }),
                  },
                ],
              },
            ]}
          >
            <IconCheckCircle size={48} color="#fff" strokeWidth={3} />
          </Animated.View>
          <Text style={styles.successTitle}>{isSms ? 'Number Verified' : 'Email Verified'}</Text>
          <Text style={styles.successSub}>Redirecting to your dashboard…</Text>
        </Animated.View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  iconWrap: {
    width: 64,
    height: 64,
    borderRadius: 18,
    backgroundColor: colors.accentLighter,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 22,
    marginBottom: 22,
  },
  title: {
    fontSize: 27,
    fontFamily: fonts.displayExtra,
    color: colors.text,
    letterSpacing: -0.5,
  },
  sub: {
    fontSize: 14.5,
    color: colors.textMuted,
    marginTop: 10,
    lineHeight: 22,
  },
  bold: { color: colors.text, fontFamily: fonts.bodySemibold },
  otpRow: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 30,
  },
  otpBox: {
    flex: 1,
    height: 58,
    textAlign: 'center',
    fontSize: 22,
    fontFamily: fonts.bodyBold,
    color: colors.text,
    borderWidth: 1.5,
    borderRadius: radii.md,
    backgroundColor: colors.bgAlt,
  },
  resendWrap: {
    marginTop: 26,
    alignItems: 'center',
  },
  resendLink: {
    color: colors.accent,
    fontFamily: fonts.bodyBold,
    fontSize: 14,
  },
  resendText: {
    fontSize: 14,
    color: colors.textMuted,
  },
  changeEmail: {
    textAlign: 'center',
    marginTop: 20,
    fontSize: 14,
    color: colors.textMuted,
    fontFamily: fonts.bodySemibold,
  },
  successOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  successCircle: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: colors.success,
    alignItems: 'center',
    justifyContent: 'center',
  },
  successTitle: {
    marginTop: 24,
    fontSize: 22,
    fontFamily: fonts.displayExtra,
    color: colors.text,
  },
  successSub: {
    marginTop: 8,
    fontSize: 14,
    color: colors.textMuted,
  },
});
