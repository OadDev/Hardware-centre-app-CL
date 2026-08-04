import { Platform } from 'react-native';

// react-native-onesignal is a native-only module (no web implementation) —
// every entry point here is a no-op on web and a no-op if the app wasn't
// built with an OneSignal App ID configured (EXPO_PUBLIC_ONESIGNAL_APP_ID),
// so this is always safe to call regardless of platform or admin setup.
const ONESIGNAL_APP_ID = process.env.EXPO_PUBLIC_ONESIGNAL_APP_ID;

let initialized = false;

export function initPush(onSubscriptionId: (id: string) => void): void {
  if (Platform.OS === 'web' || !ONESIGNAL_APP_ID || initialized) return;
  initialized = true;

  try {
    // Required native module — statically importing this at the top of a
    // file that also runs on web would break the web bundle, so it's
    // required lazily here, behind the platform guard above.
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    const { OneSignal } = require('react-native-onesignal');

    OneSignal.initialize(ONESIGNAL_APP_ID);
    OneSignal.Notifications.requestPermission(true);

    OneSignal.User.pushSubscription.addEventListener('change', (event: { current?: { id?: string | null } }) => {
      if (event?.current?.id) onSubscriptionId(event.current.id);
    });

    if (OneSignal.User.pushSubscription.id) {
      onSubscriptionId(OneSignal.User.pushSubscription.id);
    }
  } catch {
    // Push is best-effort — never let a native module issue break the app.
  }
}
