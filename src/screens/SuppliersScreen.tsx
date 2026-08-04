import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { colors, fonts, shadow } from '../theme/theme';
import { SupplierApi } from '../api/endpoints';
import { Supplier } from '../api/types';
import { BackButton } from '../components/UI';
import { IconStar, IconVerified } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function SuppliersScreen() {
  const navigation = useNavigation<Nav>();
  const [suppliers, setSuppliers] = useState<Supplier[] | null>(null);

  useEffect(() => {
    SupplierApi.list()
      .then(setSuppliers)
      .catch(() => setSuppliers([]));
  }, []);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <BackButton onPress={() => navigation.goBack()} />
        <Text style={styles.topTitle}>Top Suppliers</Text>
      </View>

      {suppliers === null ? (
        <View style={styles.center}>
          <ActivityIndicator color={colors.accent} />
        </View>
      ) : (
        <ScrollView contentContainerStyle={{ padding: 20, gap: 11 }}>
          {suppliers.map((s) => (
            <Pressable
              key={s.id}
              style={styles.supplierRow}
              onPress={() => navigation.navigate('ProductList', { title: s.name, supplier: s.name })}
            >
              <View style={styles.supplierIcon}>
                <IconVerified size={22} color={colors.bgDark} />
              </View>
              <View style={{ flex: 1, minWidth: 0 }}>
                <View style={styles.supplierNameRow}>
                  <Text style={styles.supplierName}>{s.name}</Text>
                  <IconVerified />
                </View>
                <Text style={styles.supplierMeta}>
                  {s.loc} · {s.years} yrs · GST Verified
                </Text>
              </View>
              <View style={styles.supplierRating}>
                <IconStar size={12} color={colors.success} />
                <Text style={styles.supplierRatingText}>{s.rating.toFixed(1)}</Text>
              </View>
            </Pressable>
          ))}
        </ScrollView>
      )}
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
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  supplierRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 13,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 15,
    padding: 13,
    backgroundColor: '#fff',
    ...shadow.card,
  },
  supplierIcon: {
    width: 48,
    height: 48,
    borderRadius: 13,
    backgroundColor: colors.bgAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  supplierNameRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  supplierName: { fontSize: 14.5, fontFamily: fonts.bodyBold, color: colors.text },
  supplierMeta: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
  supplierRating: { flexDirection: 'row', alignItems: 'center', gap: 3, backgroundColor: colors.successBg, paddingHorizontal: 9, paddingVertical: 5, borderRadius: 9 },
  supplierRatingText: { fontSize: 12, fontFamily: fonts.bodyBold, color: colors.successDark },
});
