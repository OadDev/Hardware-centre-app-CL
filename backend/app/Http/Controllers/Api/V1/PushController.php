<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class PushController extends Controller
{
    /**
     * Public — the OneSignal App ID isn't secret (it's the same identifier
     * OneSignal's own client SDKs expect apps to embed), unlike the REST API
     * key, which never leaves the server. Letting the app fetch this at
     * runtime means changing which OneSignal app is configured in the admin
     * console takes effect immediately, with no rebuild required.
     */
    public function config()
    {
        return response()->json([
            'app_id' => AppSetting::current()->onesignal_app_id,
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'player_id' => ['required', 'string', 'max:191'],
        ]);

        $request->user()->update(['onesignal_player_id' => $data['player_id']]);

        return response()->json(['message' => 'Registered']);
    }

    public function unregister(Request $request)
    {
        $request->user()->update(['onesignal_player_id' => null]);

        return response()->json(['message' => 'Unregistered']);
    }
}
