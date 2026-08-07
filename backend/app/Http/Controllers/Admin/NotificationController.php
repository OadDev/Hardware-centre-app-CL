<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Models\Seller;
use App\Services\NotificationService;
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
            'sms_template_id' => ['sometimes', 'nullable', 'string', 'max:60'],
        ]);
        $template->update($data);

        return response()->json($template);
    }

    public function testPush(Request $request, NotificationService $notifications)
    {
        $data = $request->validate([
            'subscription_id' => ['required', 'string'],
        ]);

        $ok = $notifications->testPush($data['subscription_id']);

        if (! $ok) {
            return response()->json(['message' => 'Could not send — check the OneSignal App ID/REST API Key are saved and the subscription ID is valid.'], 422);
        }

        return response()->json(['message' => 'Test push sent.']);
    }

    public function broadcast(Request $request, NotificationService $notifications)
    {
        $data = $request->validate([
            'audience' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $isCustomerAudience = ! in_array($data['audience'], ['All Sellers', 'Active Sellers Only'], true);

        $recipients = match ($data['audience']) {
            'All Sellers' => Seller::query()->get(['email', 'phone']),
            'Active Sellers Only' => Seller::where('account_status', 'Active')->get(['email', 'phone']),
            default => Customer::where('is_guest', false)->get(['email', 'phone', 'onesignal_player_id']),
        };

        $summary = $notifications->broadcast(
            $data['subject'],
            $data['message'],
            $recipients->map(fn ($r) => [
                'email' => $r->email,
                'phone' => $r->phone,
                'push_id' => $isCustomerAudience ? $r->onesignal_player_id : null,
            ])
        );

        $parts = $summary['email_enabled']
            ? [$summary['email_sent'].' email'.($summary['email_sent'] === 1 ? '' : 's').' sent'.($summary['email_failed'] ? ', '.$summary['email_failed'].' failed' : '')]
            : ['email skipped — channel disabled'];
        $parts[] = $summary['sms_enabled']
            ? $summary['sms_sent'].' SMS sent'.($summary['sms_failed'] ? ', '.$summary['sms_failed'].' failed' : '')
            : 'SMS skipped — channel disabled';
        $parts[] = $summary['push_enabled']
            ? $summary['push_sent'].' push notification'.($summary['push_sent'] === 1 ? '' : 's').' sent'.($summary['push_failed'] ? ', '.$summary['push_failed'].' failed' : '')
            : 'Push skipped — channel disabled';

        return response()->json(['message' => 'Broadcast to '.$recipients->count().' recipients: '.implode('; ', $parts).'.']);
    }
}
