import React from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts, radii } from '../theme/theme';
import { iconForCat } from '../components/CategoryIcon';
import { IconSearch } from '../components/Icons';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function CategoriesScreen() {
  const navigation = useNavigation<Nav>();
  const { categories: allCategories } = useApp();
  const categories = allCategories.filter((c) => !c.parent_id);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView contentContainerStyle={{ paddingHorizontal: 20, paddingBottom: 28 }}>
        <Text style={styles.title}>Categories</Text>
        <Text style={styles.sub}>Browse products across {categories.length} categories</Text>

        <Pressable style={styles.searchBar} onPress={() => navigation.navigate('Search')}>
          <IconSearch size={19} />
          <Text style={styles.searchPlaceholder}>Search in categories…</Text>
        </Pressable>

        {categories.length === 0 ? (
          <ActivityIndicator color={colors.accent} style={{ marginTop: 24 }} />
        ) : (
          <View style={styles.grid}>
            {categories.map((c) => (
              <Pressable
                key={c.id}
                style={styles.tile}
                onPress={() => navigation.navigate('CategoryListing', { catId: c.id })}
              >
                <View style={styles.iconBox}>{iconForCat(c.id)}</View>
                <Text style={styles.tileLabel}>{c.name}</Text>
                <Text style={styles.tileCount}>{c.count.toLocaleString('en-IN')} items</Text>
              </Pressable>
            ))}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  title: { fontSize: 26, fontFamily: fonts.displayExtra, color: colors.text, letterSpacing: -0.5, marginTop: 8 },
  sub: { fontSize: 13.5, color: colors.textMuted, marginTop: 4, marginBottom: 18 },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 11,
    height: 48,
    borderWidth: 1,
    borderColor: colors.borderStrong,
    borderRadius: radii.lg,
    paddingHorizontal: 15,
    backgroundColor: colors.bgAlt,
    marginBottom: 20,
  },
  searchPlaceholder: { fontSize: 14, color: colors.textFaint },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  tile: {
    width: '31.2%',
    backgroundColor: colors.bgAlt,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.xl,
    paddingVertical: 15,
    paddingHorizontal: 8,
    alignItems: 'center',
    gap: 9,
  },
  tileDark: { backgroundColor: colors.bgDark, borderColor: colors.bgDark },
  iconBox: {
    width: 48,
    height: 48,
    borderRadius: 14,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconBoxDark: { backgroundColor: 'rgba(255,255,255,0.1)', borderWidth: 0 },
  tileLabel: { fontSize: 12, fontFamily: fonts.bodyBold, color: colors.text, textAlign: 'center' },
  tileLabelDark: { color: '#fff' },
  tileCount: { fontSize: 10, color: colors.textFaint },
});
