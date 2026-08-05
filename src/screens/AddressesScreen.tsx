import React, { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { BackButton } from '../components/UI';
import { IconAddress, IconPlus } from '../components/Icons';
import AddressFormModal from '../components/AddressFormModal';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function AddressesScreen() {
  const navigation = useNavigation<Nav>();
  const { addresses } = useApp();
  const [showAddForm, setShowAddForm] = useState(false);

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

      <AddressFormModal visible={showAddForm} onClose={() => setShowAddForm(false)} />
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
});
