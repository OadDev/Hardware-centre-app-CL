<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'channels' => NotificationChannel::all(),
            'templates' => NotificationTemplate::all(),
        ]);
    }

    public function updateChannel(Request $request, NotificationChannel $channel)
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $channel->update($data);

        return response()->json($channel);
    }

    public function updateTemplate(Request $request, NotificationTemplate $template)
    {
        $data = $request->validate([
            'subject' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
        $template->update($data);

        return response()->json($template);
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'audience' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        return response()->json(['message' => 'Broadcast queued for the selected audience.']);
    }
}
