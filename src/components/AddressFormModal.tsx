import React, { useState } from 'react';
import * as Location from 'expo-location';
import { ActivityIndicator, Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { colors, fonts, radii } from '../theme/theme';
import { useApp } from '../context/AppContext';
import { Address } from '../api/types';
import { PrimaryButton } from './UI';
import StatePicker from './StatePicker';
import { IconLocation } from './Icons';

type FormState = { label: string; name: string; line: string; city: string; state: string; pincode: string };
const BLANK: FormState = { label: 'Home', name: '', line: '', city: '', state: '', pincode: '' };

export default function AddressFormModal({
  visible,
  onClose,
  onSaved,
}: {
  visible: boolean;
  onClose: () => void;
  onSaved?: (address: Address) => void;
}) {
  const { addresses, addAddress, showToast } = useApp();
  const [form, setForm] = useState<FormState>(BLANK);
  const [saving, setSaving] = useState(false);
  const [locating, setLocating] = useState(false);
  const [showChooser, setShowChooser] = useState(true);

  const close = () => {
    setForm(BLANK);
    setShowChooser(true);
    onClose();
  };

  const useCurrentLocation = async () => {
    setLocating(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        showToast('Location permission denied');
        return;
      }
      const pos = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
      const [place] = await Location.reverseGeocodeAsync({
        latitude: pos.coords.latitude,
        longitude: pos.coords.longitude,
      });
      if (place) {
        const line = [place.streetNumber, place.street, place.name].filter((v) => !!v && v !== place.city).join(' ');
        setForm((f) => ({
          ...f,
          line: line || f.line,
          city: place.city || place.subregion || f.city,
          state: place.region || f.state,
          pincode: place.postalCode || f.pincode,
        }));
        showToast('Address filled from your location — check and edit if needed');
      } else {
        showToast('Could not determine your address — enter it manually');
      }
    } catch {
      showToast('Could not get your location. Check location permissions.');
    } finally {
      setLocating(false);
      setShowChooser(false);
    }
  };

  const onSave = async () => {
    if (!form.name.trim() || !form.line.trim() || !form.city.trim() || !form.state.trim() || !form.pincode.trim()) {
      showToast('Fill in all address fields');
      return;
    }
    setSaving(true);
    try {
      const address = await addAddress({
        label: form.label || 'Home',
        name: form.name.trim(),
        line: form.line.trim(),
        city: form.city.trim(),
        state: form.state.trim(),
        pincode: form.pincode.trim(),
        is_default: addresses.length === 0,
      });
      onSaved?.(address);
      close();
    } catch (err) {
      showToast(err instanceof Error ? err.message : 'Could not save address');
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={close}>
      <View style={styles.backdrop}>
        <View style={styles.sheet}>
          <Text style={styles.title}>Add New Address</Text>

          {showChooser ? (
            <View style={{ gap: 12 }}>
              <Text style={styles.chooserSub}>How would you like to add your address?</Text>
              <Pressable style={styles.chooserBtn} onPress={useCurrentLocation} disabled={locating}>
                {locating ? <ActivityIndicator color="#fff" /> : <IconLocation size={18} color="#fff" />}
                <Text style={styles.chooserBtnText}>{locating ? 'Getting your location…' : 'Use Current Location'}</Text>
              </Pressable>
              <Pressable style={styles.chooserBtnAlt} onPress={() => setShowChooser(false)}>
                <Text style={styles.chooserBtnAltText}>Enter Address Manually</Text>
              </Pressable>
              <Pressable style={styles.cancelLink} onPress={close}>
                <Text style={styles.cancelLinkText}>Cancel</Text>
              </Pressable>
            </View>
          ) : (
            <>
              <ScrollView keyboardShouldPersistTaps="handled" contentContainerStyle={{ gap: 12 }}>
                <TextInput style={styles.input} placeholder="Label (Home / Shop / Warehouse)" value={form.label} onChangeText={(v) => setForm((f) => ({ ...f, label: v }))} placeholderTextColor={colors.textFaint} />
                <TextInput style={styles.input} placeholder="Contact name" value={form.name} onChangeText={(v) => setForm((f) => ({ ...f, name: v }))} placeholderTextColor={colors.textFaint} />
                <TextInput style={styles.input} placeholder="Address line" value={form.line} onChangeText={(v) => setForm((f) => ({ ...f, line: v }))} placeholderTextColor={colors.textFaint} />
                <TextInput style={styles.input} placeholder="City" value={form.city} onChangeText={(v) => setForm((f) => ({ ...f, city: v }))} placeholderTextColor={colors.textFaint} />
                <StatePicker value={form.state} onChange={(v) => setForm((f) => ({ ...f, state: v }))} />
                <TextInput style={styles.input} placeholder="Pincode" value={form.pincode} onChangeText={(v) => setForm((f) => ({ ...f, pincode: v }))} keyboardType="number-pad" placeholderTextColor={colors.textFaint} />
              </ScrollView>
              <View style={{ flexDirection: 'row', gap: 10, marginTop: 16 }}>
                <Pressable style={styles.modalCancelBtn} onPress={close}>
                  <Text style={styles.modalCancelText}>Cancel</Text>
                </Pressable>
                <View style={{ flex: 1 }}>
                  <PrimaryButton label="Save Address" onPress={onSave} loading={saving} />
                </View>
              </View>
            </>
          )}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: { flex: 1, backgroundColor: 'rgba(15,20,28,0.5)', justifyContent: 'flex-end' },
  sheet: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '85%' },
  title: { fontSize: 18, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 16 },
  chooserSub: { fontSize: 13.5, color: colors.textMuted, marginBottom: 2 },
  chooserBtn: {
    height: 52,
    borderRadius: radii.md,
    backgroundColor: colors.accent,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 9,
  },
  chooserBtnText: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: '#fff' },
  chooserBtnAlt: {
    height: 52,
    borderRadius: radii.md,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    alignItems: 'center',
    justifyContent: 'center',
  },
  chooserBtnAltText: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.textSub },
  cancelLink: { alignItems: 'center', paddingVertical: 6 },
  cancelLinkText: { fontSize: 13.5, fontFamily: fonts.bodySemibold, color: colors.textMuted },
  input: {
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
