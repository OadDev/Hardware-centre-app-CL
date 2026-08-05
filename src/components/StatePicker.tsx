import React, { useMemo, useState } from 'react';
import { FlatList, Modal, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { colors, fonts, radii } from '../theme/theme';
import { INDIAN_STATES } from '../data/indianStates';
import { IconChevronDown, IconSearch } from './Icons';

export default function StatePicker({ value, onChange }: { value: string; onChange: (state: string) => void }) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return INDIAN_STATES;
    return INDIAN_STATES.filter((s) => s.toLowerCase().includes(q));
  }, [query]);

  return (
    <>
      <Pressable style={styles.field} onPress={() => setOpen(true)}>
        <Text style={value ? styles.fieldText : styles.fieldPlaceholder}>{value || 'State'}</Text>
        <IconChevronDown />
      </Pressable>

      <Modal visible={open} animationType="slide" transparent onRequestClose={() => setOpen(false)}>
        <View style={styles.backdrop}>
          <View style={styles.sheet}>
            <Text style={styles.title}>Select State</Text>
            <View style={styles.searchRow}>
              <IconSearch size={16} />
              <TextInput
                style={styles.searchInput}
                placeholder="Search state"
                value={query}
                onChangeText={setQuery}
                placeholderTextColor={colors.textFaint}
                autoFocus
              />
            </View>
            <FlatList
              data={filtered}
              keyExtractor={(item) => item}
              style={{ maxHeight: 360 }}
              keyboardShouldPersistTaps="handled"
              renderItem={({ item }) => (
                <Pressable
                  style={styles.option}
                  onPress={() => {
                    onChange(item);
                    setQuery('');
                    setOpen(false);
                  }}
                >
                  <Text style={[styles.optionText, item === value ? styles.optionTextActive : null]}>{item}</Text>
                </Pressable>
              )}
              ListEmptyComponent={<Text style={styles.empty}>No states match "{query}"</Text>}
            />
            <Pressable style={styles.cancelBtn} onPress={() => setOpen(false)}>
              <Text style={styles.cancelText}>Cancel</Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  field: {
    height: 48,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    borderRadius: radii.md,
    paddingHorizontal: 14,
    backgroundColor: colors.bgAlt,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  fieldText: { fontSize: 14, color: colors.text, fontFamily: fonts.bodySemibold },
  fieldPlaceholder: { fontSize: 14, color: colors.textFaint },
  backdrop: { flex: 1, backgroundColor: 'rgba(15,20,28,0.5)', justifyContent: 'flex-end' },
  sheet: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '75%' },
  title: { fontSize: 18, fontFamily: fonts.displayExtra, color: colors.text, marginBottom: 14 },
  searchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderWidth: 1.5,
    borderColor: colors.borderStrong,
    borderRadius: radii.md,
    paddingHorizontal: 12,
    height: 44,
    marginBottom: 10,
  },
  searchInput: { flex: 1, fontSize: 14, color: colors.text },
  option: { paddingVertical: 13, borderBottomWidth: 1, borderBottomColor: colors.divider },
  optionText: { fontSize: 14.5, color: colors.text },
  optionTextActive: { fontFamily: fonts.bodyBold, color: colors.accentDark },
  empty: { textAlign: 'center', color: colors.textMuted, fontSize: 13, paddingVertical: 24 },
  cancelBtn: { marginTop: 12, height: 48, alignItems: 'center', justifyContent: 'center', borderRadius: radii.md, borderWidth: 1.5, borderColor: colors.borderStrong },
  cancelText: { fontSize: 15, fontFamily: fonts.bodyBold, color: colors.textSub },
});
