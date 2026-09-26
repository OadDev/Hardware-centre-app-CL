import React, { useState } from 'react';
import { Alert, Linking, Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { inr } from '../utils/format';
import { PrimaryButton } from '../components/UI';
import { PRIVACY_POLICY_URL, TERMS_URL } from '../api/config';
import {
  IconAddress,
  IconChevronRight,
  IconEdit,
  IconGst,
  IconHelp,
  IconLogout,
  IconOrders,
  IconVerified,
  IconWishlist,
} from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function ProfileScreen() {
  const navigation = useNavigation<Nav>();
  const { user, orders, wishlist, logout, deleteAccount, showToast, updateProfile } = useApp();
  const [showEdit, setShowEdit] = useState(false);
  const [form, setForm] = useState({ name: user.name, business_name: user.businessName, city: user.city, state: user.state });
  const [saving, setSaving] = useState(false);

  const savedTotal = orders.reduce((sum, o) => sum + o.discount, 0);

  const onLogout = async () => {
    await logout();
    navigation.reset({ index: 0, routes: [{ name: 'Welcome' }] });
  };

  const onDeleteAccount = () => {
    Alert.alert(
      'Delete account?',
      'This permanently deletes your account, saved addresses, wishlist and cart. Past orders and invoices are kept anonymised for tax records. This cannot be undone.',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            if (await deleteAccount()) {
              showToast('Your account has been deleted');
              navigation.reset({ index: 0, routes: [{ name: 'Welcome' }] });
            }
          },
        },
      ],
    );
  };

  const openEdit = () => {
    setForm({ name: user.name, business_name: user.businessName, city: user.city, state: user.state });
    setShowEdit(true);
  };

  const onSaveProfile = async () => {
    if (!form.name.trim()) {
      showToast('Enter your name');
      return;
    }
    setSaving(true);
    const ok = await updateProfile({
      name: form.name.trim(),
      business_name: form.business_name.trim(),
      city: form.city.trim(),
      state: form.state.trim(),
    });
    setSaving(false);
    if (ok) setShowEdit(false);
  };

  const menuMain = [
    { icon: <IconOrders size={19} />, label: 'My Orders', onPress: () => navigation.navigate('Main', { screen: 'Orders' }) },
    { icon: <IconWishlist />, label: 'My Wishlist', onPress: () => navigation.navigate('Wishlist') },
    { icon: <IconAddress />, label: 'Saved Addresses', onPress: () => navigation.navigate('Addresses') },
    { icon: <IconGst />, label: 'GST & Business Details', onPress: () => showToast(user.gst ? `GST: ${user.gst}` : 'No GST number on file') },
  ];

  const menuSecondary = [
    { icon: <IconOrders size={19} />, label: 'Notifications', onPress: () => showToast('No new notifications') },
    { icon: <IconHelp />, label: 'Help & Support', onPress: () => Linking.openURL('mailto:help@hardwarecentre.in') },
    { icon: <IconHelp />, label: 'Privacy Policy', onPress: () => Linking.openURL(PRIVACY_POLICY_URL) },
    { icon: <IconHelp />, label: 'Terms of Service', onPress: () => Linking.openURL(TERMS_URL) },
  ];

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView contentContainerStyle={{ paddingBottom: 24 }}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Profile</Text>
          <View style={styles.profileRow}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>{user.name[0]}</Text>
            </View>
            <View style={{ flex: 1, minWidth: 0 }}>
              <View style={styles.nameRow}>
                <Text style={styles.name}>{user.name}</Text>
                {!user.isGuest && <IconVerified />}
              </View>
              <Text style={styles.email}>{user.isGuest ? 'Guest session' : user.email}</Text>
              {!user.isGuest && <Text style={styles.gst}>GST: {user.gst}</Text>}
            </View>
            <Pressable style={styles.editBtn} onPress={openEdit}>
              <IconEdit />
            </Pressable>
          </View>
        </View>

        <View style={styles.statsCard}>
          <Pressable style={styles.statItem} onPress={() => navigation.navigate('Main', { screen: 'Orders' })}>
            <Text style={styles.statNum}>{orders.length}</Text>
            <Text style={styles.statLabel}>Orders</Text>
          </Pressable>
          <View style={styles.statDivider} />
          <View style={styles.statItem}>
            <Text style={styles.statNum}>{Object.keys(wishlist).length}</Text>
            <Text style={styles.statLabel}>Wishlist</Text>
          </View>
          <View style={styles.statDivider} />
          <View style={styles.statItem}>
            <Text style={[styles.statNum, { color: colors.successDark }]}>{inr(savedTotal)}</Text>
            <Text style={styles.statLabel}>Saved</Text>
          </View>
        </View>

        <View style={styles.menuBox}>
          {menuMain.map((m, i) => (
            <Pressable key={m.label} onPress={m.onPress} style={[styles.menuRow, i === menuMain.length - 1 ? { borderBottomWidth: 0 } : null]}>
              <View style={styles.menuIcon}>{m.icon}</View>
              <Text style={styles.menuLabel}>{m.label}</Text>
              <IconChevronRight />
            </Pressable>
          ))}
        </View>

        <View style={[styles.menuBox, { marginTop: 14 }]}>
          {menuSecondary.map((m, i) => (
            <Pressable key={m.label} onPress={m.onPress} style={[styles.menuRow, i === menuSecondary.length - 1 ? { borderBottomWidth: 0 } : null]}>
              <View style={styles.menuIcon}>{m.icon}</View>
              <Text style={styles.menuLabel}>{m.label}</Text>
              <IconChevronRight />
            </Pressable>
          ))}
        </View>

        <Pressable style={styles.logoutBtn} onPress={onLogout}>
          <IconLogout />
          <Text style={styles.logoutText}>Logout</Text>
        </Pressable>
        {!user.isGuest && (
          <Pressable style={styles.deleteBtn} onPress={onDeleteAccount}>
            <Text style={styles.deleteText}>Delete Account</Text>
          </Pressable>
        )}
        <Text style={styles.version}>Hardware Centers · Version 1.0.0</Text>
      </ScrollView>

      <Modal visible={showEdit} animationType="slide" transparent onRequestClose={() => setShowEdit(false)}>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalSheet}>
            <Text style={styles.modalTitle}>Edit Profile</Text>
            <View style={{ gap: 12 }}>
              <TextInput
                style={styles.modalInput}
                placeholder="Full name"
                value={form.name}
                onChangeText={(v) => setForm((f) => ({ ...f, name: v }))}
                placeholderTextColor={colors.textFaint}
              />
              <TextInput
                style={styles.modalInput}
                placeholder="Business name"
                value={form.business_name}
                onChangeText={(v) => setForm((f) => ({ ...f, business_name: v }))}
                placeholderTextColor={colors.textFaint}
              />
              <TextInput
                style={styles.modalInput}
                placeholder="City"
                value={form.city}
                onChangeText={(v) => setForm((f) => ({ ...f, city: v }))}
                placeholderTextColor={colors.textFaint}
              />
              <TextInput
                style={styles.modalInput}
                placeholder="State"
                value={form.state}
                onChangeText={(v) => setForm((f) => ({ ...f, state: v }))}
                placeholderTextColor={colors.textFaint}
              />
            </View>
            <View style={{ flexDirection: 'row', gap: 10, marginTop: 16 }}>
              <Pressable style={styles.modalCancelBtn} onPress={() => setShowEdit(false)}>
                <Text style={styles.modalCancelText}>Cancel</Text>
              </Pressable>
              <View style={{ flex: 1 }}>
                <PrimaryButton label="Save" onPress={onSaveProfile} loading={saving} />
              </View>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bgAlt },
  header: { backgroundColor: colors.bgDark, paddingHorizontal: 20, paddingTop: 22, paddingBottom: 30 },
  headerTitle: { fontSize: 20, fontFamily: fonts.displayExtra, color: '#fff', letterSpacing: -0.3, marginBottom: 18 },
  profileRow: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  avatar: { width: 60, height: 60, borderRadius: 18, backgroundColor: colors.accent, alignItems: 'center', justifyContent: 'center' },
  avatarText: { color: '#fff', fontFamily: fonts.displayExtra, fontSize: 24 },
  nameRow: { flexDirection: 'row', alignItems: 'center', gap: 7 },
  name: { fontSize: 16.5, fontFamily: fonts.bodyBold, color: '#fff' },
  email: { fontSize: 12.5, color: '#9CA3AF', marginTop: 3 },
  gst: { fontSize: 11, color: colors.accentSoft, marginTop: 4, fontFamily: fonts.bodySemibold },
  editBtn: { width: 38, height: 38, borderRadius: 11, backgroundColor: 'rgba(255,255,255,0.1)', alignItems: 'center', justifyContent: 'center' },
  statsCard: {
    marginHorizontal: 20,
    marginTop: -16,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.xl,
    padding: 16,
    flexDirection: 'row',
    shadowColor: '#111827',
    shadowOpacity: 0.05,
    shadowRadius: 14,
    shadowOffset: { width: 0, height: 4 },
    elevation: 3,
  },
  statItem: { flex: 1, alignItems: 'center' },
  statNum: { fontSize: 19, fontFamily: fonts.displayExtra, color: colors.text },
  statLabel: { fontSize: 11, color: colors.textMuted, marginTop: 2 },
  statDivider: { width: 1, backgroundColor: colors.divider },
  menuBox: { marginHorizontal: 20, marginTop: 20, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.border, borderRadius: radii.xl, overflow: 'hidden' },
  menuRow: { flexDirection: 'row', alignItems: 'center', gap: 13, padding: 15, borderBottomWidth: 1, borderBottomColor: '#F4F6F8' },
  menuIcon: { width: 38, height: 38, borderRadius: 11, backgroundColor: colors.bgAlt, alignItems: 'center', justifyContent: 'center' },
  menuLabel: { flex: 1, fontSize: 14, fontFamily: fonts.bodySemibold, color: colors.text },
  logoutBtn: {
    marginHorizontal: 20,
    marginTop: 18,
    height: 50,
    borderWidth: 1.5,
    borderColor: colors.dangerBorder,
    borderRadius: 13,
    backgroundColor: '#fff',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 9,
  },
  logoutText: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.danger },
  deleteBtn: { marginHorizontal: 20, marginTop: 10, height: 44, alignItems: 'center', justifyContent: 'center' },
  deleteText: { fontSize: 13, fontFamily: fonts.bodySemibold, color: colors.textMuted, textDecorationLine: 'underline' },
  version: { textAlign: 'center', marginTop: 18, fontSize: 11, color: colors.textFaint },
  modalBackdrop: { flex: 1, backgroundColor: 'rgba(15,20,28,0.5)', justifyContent: 'flex-end' },
  modalSheet: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
  modalTitle: { fontSize: 18, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 16 },
  modalInput: {
    height: 48,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    borderRadius: radii.md,
    paddingHorizontal: 14,
    fontSize: 14,
    color: colors.text,
    backgroundColor: colors.bgAlt,
  },
  modalCancelBtn: { height: 54, paddingHorizontal: 20, borderRadius: radii.md, borderWidth: 1.5, borderColor: colors.borderStrong, alignItems: 'center', justifyContent: 'center' },
  modalCancelText: { fontSize: 15, fontFamily: fonts.bodyBold, color: colors.textSub },
});
