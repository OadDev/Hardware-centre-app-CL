import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MainTabParamList } from './types';
import HomeScreen from '../screens/HomeScreen';
import CategoriesScreen from '../screens/CategoriesScreen';
import CartScreen from '../screens/CartScreen';
import OrdersScreen from '../screens/OrdersScreen';
import ProfileScreen from '../screens/ProfileScreen';
import { colors, fonts } from '../theme/theme';
import { IconCart, IconHome, IconGrid, IconOrders, IconUser } from '../components/Icons';
import { useApp } from '../context/AppContext';

const Tab = createBottomTabNavigator<MainTabParamList>();

function CartIconWithBadge({ color, size }: { color: string; size: number }) {
  const { totals } = useApp();
  return (
    <View>
      <IconCart size={size} color={color} strokeWidth={2} />
      {totals.count > 0 && (
        <View style={styles.badge}>
          <Text style={styles.badgeText}>{totals.count}</Text>
        </View>
      )}
    </View>
  );
}

export default function MainTabs() {
  const insets = useSafeAreaInsets();
  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.accent,
        tabBarInactiveTintColor: colors.textMuted,
        tabBarStyle: [styles.tabBar, { height: 54 + insets.bottom, paddingBottom: Math.max(8, insets.bottom) }],
        tabBarLabelStyle: styles.tabLabel,
      }}
    >
      <Tab.Screen
        name="Home"
        component={HomeScreen}
        options={{ tabBarIcon: ({ color, size }) => <IconHome color={color} size={size} strokeWidth={2} /> }}
      />
      <Tab.Screen
        name="Categories"
        component={CategoriesScreen}
        options={{ tabBarIcon: ({ color, size }) => <IconGrid color={color} size={size} strokeWidth={2} /> }}
      />
      <Tab.Screen
        name="Cart"
        component={CartScreen}
        options={{ tabBarIcon: ({ color, size }) => <CartIconWithBadge color={color} size={size} /> }}
      />
      <Tab.Screen
        name="Orders"
        component={OrdersScreen}
        options={{ tabBarIcon: ({ color, size }) => <IconOrders color={color} size={size} strokeWidth={2} /> }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileScreen}
        options={{ tabBarIcon: ({ color, size }) => <IconUser color={color} size={size} strokeWidth={2} /> }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  tabBar: {
    borderTopColor: colors.border,
    height: 62,
    paddingTop: 8,
    paddingBottom: 8,
  },
  tabLabel: {
    fontSize: 10.5,
    fontFamily: fonts.bodySemibold,
  },
  badge: {
    position: 'absolute',
    top: -6,
    right: -9,
    minWidth: 17,
    height: 17,
    borderRadius: 9,
    backgroundColor: colors.accent,
    borderWidth: 2,
    borderColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontFamily: fonts.bodyBold,
  },
});
