import React, { useEffect, useRef, useState } from 'react';
import { Dimensions, FlatList, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootStackParamList } from '../navigation/types';
import { useApp } from '../context/AppContext';
import { colors, fonts } from '../theme/theme';
import { OnboardingApi } from '../api/endpoints';
import { PrimaryButton } from '../components/UI';
import PlaceholderImage from '../components/PlaceholderImage';

const { width } = Dimensions.get('window');

type Panel = { id: string; title: string; sub: string; ph: string; image_url: string | null };

const DEFAULT_PANELS: Panel[] = [
  { id: 'ob-1', ph: 'Warehouse & tools', title: 'Everything Hardware in One Place', sub: 'Browse thousands of products from verified suppliers across India.', image_url: null },
  { id: 'ob-2', ph: 'Supplier network', title: 'Verified Suppliers', sub: 'Trade with trusted distributors and manufacturers you can rely on.', image_url: null },
  { id: 'ob-3', ph: 'Products & pricing', title: 'Best Prices', sub: 'Compare quotes from multiple sellers and save more on every order.', image_url: null },
  { id: 'ob-4', ph: 'Delivery truck', title: 'Fast Delivery', sub: 'Track every order from the warehouse to your doorstep in real time.', image_url: null },
];

type Props = NativeStackScreenProps<RootStackParamList, 'Onboarding'>;

export default function OnboardingScreen({ navigation }: Props) {
  const { completeOnboarding } = useApp();
  const [index, setIndex] = useState(0);
  const [panels, setPanels] = useState<Panel[]>(DEFAULT_PANELS);
  const listRef = useRef<FlatList>(null);

  useEffect(() => {
    OnboardingApi.list()
      .then((slides) => {
        if (slides.length > 0) {
          setPanels(
            slides.map((s) => ({
              id: String(s.id),
              title: s.title,
              sub: s.subtitle || '',
              ph: s.title,
              image_url: s.image_url,
            }))
          );
        }
      })
      .catch(() => {
        // Keep the built-in default panels — onboarding must never block on the network.
      });
  }, []);

  const finish = () => {
    completeOnboarding();
    navigation.replace('Welcome');
  };

  const next = () => {
    if (index < panels.length - 1) {
      listRef.current?.scrollToIndex({ index: index + 1, animated: true });
      setIndex(index + 1);
    } else {
      finish();
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <View style={styles.skipRow}>
        <Pressable onPress={finish} hitSlop={8}>
          <Text style={styles.skip}>Skip</Text>
        </Pressable>
      </View>

      <FlatList
        ref={listRef}
        data={panels}
        keyExtractor={(item) => item.id}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onMomentumScrollEnd={(e) => {
          const i = Math.round(e.nativeEvent.contentOffset.x / width);
          setIndex(i);
        }}
        renderItem={({ item }) => (
          <View style={{ width, paddingHorizontal: 32 }}>
            {item.image_url ? (
              <Image source={{ uri: item.image_url }} style={styles.panelImage} resizeMode="cover" />
            ) : (
              <PlaceholderImage seed={item.id} label={item.ph} radius={24} style={styles.panelImage} />
            )}
            <Text style={styles.title}>{item.title}</Text>
            <Text style={styles.sub}>{item.sub}</Text>
          </View>
        )}
      />

      <View style={styles.bottom}>
        <View style={styles.dots}>
          {panels.map((_, i) => (
            <View key={i} style={[styles.dot, i === index ? styles.dotActive : null]} />
          ))}
        </View>
        <PrimaryButton label={index === panels.length - 1 ? 'Get Started' : 'Next'} onPress={next} />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
  },
  skipRow: {
    alignItems: 'flex-end',
    paddingHorizontal: 22,
    paddingTop: 8,
    paddingBottom: 6,
  },
  skip: {
    color: colors.textMuted,
    fontSize: 14,
    fontFamily: fonts.bodySemibold,
  },
  panelImage: {
    width: '100%',
    height: 330,
    borderRadius: 24,
  },
  title: {
    marginTop: 40,
    fontSize: 27,
    fontFamily: fonts.displayExtra,
    color: colors.text,
    lineHeight: 32,
    letterSpacing: -0.5,
  },
  sub: {
    marginTop: 14,
    fontSize: 15,
    color: colors.textMuted,
    lineHeight: 23,
  },
  bottom: {
    paddingHorizontal: 32,
    paddingBottom: 20,
  },
  dots: {
    flexDirection: 'row',
    gap: 7,
    justifyContent: 'center',
    marginBottom: 30,
  },
  dot: {
    height: 7,
    width: 7,
    borderRadius: 4,
    backgroundColor: colors.borderStrong,
  },
  dotActive: {
    width: 22,
    backgroundColor: colors.accent,
  },
});
