import { Platform } from 'react-native';

// Android emulators reach the host machine at 10.0.2.2; iOS simulators and
// Expo web can use localhost directly. Override by editing this file if
// testing against a LAN-hosted backend from a physical device.
const DEV_HOST = Platform.OS === 'android' ? '10.0.2.2' : 'localhost';

export const API_BASE_URL = `http://${DEV_HOST}:8000/api/v1`;
