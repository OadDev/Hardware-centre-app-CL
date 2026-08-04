<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushController extends Controller
{
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
