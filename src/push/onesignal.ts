import { Platform } from 'react-native';
import { http } from '../api/client';

// react-native-onesignal is a native-only module (no web implementation) —
// every entry point here is a no-op on web, so this is always safe to call
// regardless of platform. The App ID itself is fetched from the backend
// rather than baked in at build time: it isn't secret (OneSignal's own
// client SDKs expect apps to embed it), so whatever the admin has saved
// under Notification Management takes effect the next time the app opens —
// no rebuild needed to change which OneSignal app is wired up.
let initialized = false;

export async function initPush(onSubscriptionId: (id: string) => void): Promise<void> {
  if (Platform.OS === 'web' || initialized) return;
  initialized = true;

  try {
    const { app_id } = await http.get<{ app_id: string | null }>('/push/config');
    if (!app_id) return;

    // Required native module — statically importing this at the top of a
    // file that also runs on web would break the web bundle, so it's
    // required lazily here, behind the platform guard above.
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    const { OneSignal } = require('react-native-onesignal');

    OneSignal.initialize(app_id);
    OneSignal.Notifications.requestPermission(true);

    OneSignal.User.pushSubscription.addEventListener('change', (event: { current?: { id?: string | null } }) => {
      if (event?.current?.id) onSubscriptionId(event.current.id);
    });

    if (OneSignal.User.pushSubscription.id) {
      onSubscriptionId(OneSignal.User.pushSubscription.id);
    }
  } catch {
    // Push is best-effort — never let a network hiccup or native module
    // issue break the app.
  }
}
