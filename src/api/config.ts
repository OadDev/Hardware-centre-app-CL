import { Platform } from 'react-native';

// Android emulators reach the host machine at 10.0.2.2; iOS simulators and
// Expo web can use localhost directly. This default only works for local
// development against a dev server running on the same machine as the
// emulator — it will NOT work on a real device or in a built app.
//
// For a real build (EAS build, TestFlight, Play Store, or a physical device
// on your LAN), set EXPO_PUBLIC_API_URL to your deployed backend's URL,
// e.g. in a .env file: EXPO_PUBLIC_API_URL=https://api.hardwarecentre.in/api/v1
const DEV_HOST = Platform.OS === 'android' ? '10.0.2.2' : 'localhost';
const DEFAULT_DEV_URL = `http://${DEV_HOST}:8000/api/v1`;

export const API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || DEFAULT_DEV_URL;
