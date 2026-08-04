import React, { useState } from 'react';
import { Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { BackButton, PrimaryButton } from '../components/UI';
import { IconAddress, IconPlus } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function AddressesScreen() {
  const navigation = useNavigation<Nav>();
  const { addresses, addAddress, showToast } = useApp();
  const [showAddForm, setShowAddForm] = useState(false);
  const [form, setForm] = useState({ name: '', line: '', city: '', state: '', pincode: '', label: 'Home' });
  const [saving, setSaving] = useState(false);

  const onSaveAddress = async () => {
    if (!form.name.trim() || !form.line.trim() || !form.city.trim() || !form.state.trim() || !form.pincode.trim()) {
      showToast('Fill in all address fields');
      return;
    }
    setSaving(true);
    try {
      await addAddress({
        label: form.label || 'Home',
        name: form.name.trim(),
        line: form.line.trim(),
        city: form.city.trim(),
        state: form.state.trim(),
        pincode: form.pincode.trim(),
        is_default: addresses.length === 0,
      });
      setShowAddForm(false);
      setForm({ name: '', line: '', city: '', state: '', pincode: '', label: 'Home' });
    } catch (err) {
      showToast(err instanceof Error ? err.message : 'Could not save address');
    } finally {
      setSaving(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>Saved Addresses</Text>
      </View>

      <ScrollView contentContainerStyle={{ padding: 20, gap: 11 }}>
        {addresses.length === 0 ? (
          <View style={styles.center}>
            <IconAddress size={40} color={colors.textFaint} />
            <Text style={styles.emptyTitle}>No saved addresses</Text>
            <Text style={styles.emptySub}>Add a delivery address to speed up checkout.</Text>
          </View>
        ) : (
          addresses.map((a) => (
            <View key={a.id} style={styles.addressCard}>
              <View style={styles.addressTopRow}>
                <Text style={styles.addressName}>{a.name}</Text>
                <View style={styles.addressLabel}>
                  <Text style={styles.addressLabelText}>{a.label}</Text>
                </View>
                {a.is_default && (
                  <View style={styles.defaultLabel}>
                    <Text style={styles.defaultLabelText}>Default</Text>
                  </View>
                )}
              </View>
              <Text style={styles.addressLine}>{a.line}, {a.city}, {a.state} {a.pincode}</Text>
            </View>
          ))
        )}
        <Pressable style={styles.addAddressBtn} onPress={() => setShowAddForm(true)}>
          <IconPlus size={17} color={colors.textSub} strokeWidth={2.2} />
          <Text style={styles.addAddressText}>Add New Address</Text>
        </Pressable>
      </ScrollView>

      <Modal visible={showAddForm} animationType="slide" transparent onRequestClose={() => setShowAddForm(false)}>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalSheet}>
            <Text style={styles.modalTitle}>Add New Address</Text>
            <ScrollView keyboardShouldPersistTaps="handled" contentContainerStyle={{ gap: 12 }}>
              <TextInput style={styles.modalInput} placeholder="Label (Home / Shop / Warehouse)" value={form.label} onChangeText={(v) => setForm((f) => ({ ...f, label: v }))} placeholderTextColor={colors.textFaint} />
              <TextInput style={styles.modalInput} placeholder="Contact name" value={form.name} onChangeText={(v) => setForm((f) => ({ ...f, name: v }))} placeholderTextColor={colors.textFaint} />
              <TextInput style={styles.modalInput} placeholder="Address line" value={form.line} onChangeText={(v) => setForm((f) => ({ ...f, line: v }))} placeholderTextColor={colors.textFaint} />
              <TextInput style={styles.modalInput} placeholder="City" value={form.city} onChangeText={(v) => setForm((f) => ({ ...f, city: v }))} placeholderTextColor={colors.textFaint} />
              <TextInput style={styles.modalInput} placeholder="State" value={form.state} onChangeText={(v) => setForm((f) => ({ ...f, state: v }))} placeholderTextColor={colors.textFaint} />
              <TextInput style={styles.modalInput} placeholder="Pincode" value={form.pincode} onChangeText={(v) => setForm((f) => ({ ...f, pincode: v }))} keyboardType="number-pad" placeholderTextColor={colors.textFaint} />
            </ScrollView>
            <View style={{ flexDirection: 'row', gap: 10, marginTop: 16 }}>
              <Pressable style={styles.modalCancelBtn} onPress={() => setShowAddForm(false)}>
                <Text style={styles.modalCancelText}>Cancel</Text>
              </Pressable>
              <View style={{ flex: 1 }}>
                <PrimaryButton label="Save Address" onPress={onSaveAddress} loading={saving} />
              </View>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  topTitle: { flex: 1, fontSize: 17, fontFamily: fonts.bodyBold, color: colors.text, letterSpacing: -0.2 },
  center: { alignItems: 'center', justifyContent: 'center', paddingVertical: 60, paddingHorizontal: 20, gap: 8 },
  emptyTitle: { fontSize: 16, fontFamily: fonts.bodyBold, color: colors.text, marginTop: 8 },
  emptySub: { fontSize: 13, color: colors.textMuted, textAlign: 'center' },
  addressCard: { borderWidth: 1, borderColor: colors.border, borderRadius: radii.lg, padding: 14, backgroundColor: '#fff' },
  addressTopRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  addressName: { fontSize: 14, fontFamily: fonts.bodyBold, color: colors.text },
  addressLabel: { backgroundColor: colors.accentLight, paddingHorizontal: 7, paddingVertical: 2, borderRadius: 6 },
  addressLabelText: { fontSize: 10, fontFamily: fonts.bodyBold, color: colors.accentDark },
  defaultLabel: { backgroundColor: colors.successBg, paddingHorizontal: 7, paddingVertical: 2, borderRadius: 6 },
  defaultLabelText: { fontSize: 10, fontFamily: fonts.bodyBold, color: colors.successDark },
  addressLine: { fontSize: 12.5, color: colors.textMuted, marginTop: 5, lineHeight: 18 },
  addAddressBtn: { borderWidth: 1.5, borderStyle: 'dashed', borderColor: colors.borderStrong, borderRadius: radii.lg, padding: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
  addAddressText: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.textSub },
  modalBackdrop: { flex: 1, backgroundColor: 'rgba(15,20,28,0.5)', justifyContent: 'flex-end' },
  modalSheet: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '80%' },
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
