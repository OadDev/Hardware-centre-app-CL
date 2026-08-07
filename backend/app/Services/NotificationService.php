<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\AppSetting;
use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Support\SmtpMailer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Renders the message templates configured under Notification Management
 * and actually delivers them over whichever channels the admin has enabled
 * and configured (Email via SMTP, SMS via Fast2SMS, Push via OneSignal). A
 * channel that's toggled on but has no working credentials is silently
 * skipped rather than failing the caller's underlying action (an order
 * still gets placed even if its confirmation email can't be sent).
 */
class NotificationService
{
    public function notify(string $templateKey, ?string $email, ?string $phone, array $vars = [], ?string $pushPlayerId = null): void
    {
        $template = NotificationTemplate::where('key', $templateKey)->first();
        if (! $template || ! trim((string) $template->body)) {
            return;
        }

        $subject = $this->render($template->subject ?: $template->name, $vars);
        $body = $this->render($template->body, $vars);

        if ($email && $this->channelEnabled('email')) {
            $this->trySendEmail($email, $subject, $body);
        }
        if ($phone && $this->channelEnabled('sms')) {
            $this->trySendTemplatedSms($phone, $template, $vars);
        }
        if ($pushPlayerId && $this->channelEnabled('push')) {
            $this->trySendPush($pushPlayerId, $subject, $body);
        }
    }

    /**
     * Ad-hoc broadcast to many recipients at once (Notification Management >
     * New Broadcast). Returns a per-channel delivery count instead of
     * silently claiming success for channels that were skipped.
     *
     * @param  iterable<array{email?: ?string, phone?: ?string, push_id?: ?string}>  $recipients
     */
    public function broadcast(string $subject, string $message, iterable $recipients): array
    {
        $emailOn = $this->channelEnabled('email');
        $smsOn = $this->channelEnabled('sms');
        $pushOn = $this->channelEnabled('push');
        $summary = [
            'email_sent' => 0, 'email_failed' => 0, 'email_enabled' => $emailOn,
            'sms_sent' => 0, 'sms_failed' => 0, 'sms_enabled' => $smsOn,
            'push_sent' => 0, 'push_failed' => 0, 'push_enabled' => $pushOn,
        ];

        foreach ($recipients as $r) {
            if ($emailOn && ! empty($r['email'])) {
                $this->trySendEmail($r['email'], $subject, $message) ? $summary['email_sent']++ : $summary['email_failed']++;
            }
            if ($smsOn && ! empty($r['phone'])) {
                $this->trySendSms($r['phone'], $message) ? $summary['sms_sent']++ : $summary['sms_failed']++;
            }
            if ($pushOn && ! empty($r['push_id'])) {
                $this->trySendPush($r['push_id'], $subject, $message) ? $summary['push_sent']++ : $summary['push_failed']++;
            }
        }

        return $summary;
    }

    /**
     * Sends a fixed test push directly to one subscription ID, bypassing the
     * "push" channel toggle — this is an explicit admin action to verify
     * OneSignal credentials, not a real customer notification.
     */
    public function testPush(string $subscriptionId): bool
    {
        return $this->trySendPush($subscriptionId, 'Test Notification', 'This is a test push from the Hardware Centers admin console.');
    }

    private function render(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
        }

        return $text;
    }

    private function channelEnabled(string $key): bool
    {
        return (bool) NotificationChannel::where('key', $key)->value('enabled');
    }

    private function trySendEmail(string $email, string $subject, string $body): bool
    {
        if (! SmtpMailer::configure()) {
            return false;
        }

        try {
            Mail::to($email)->send(new NotificationMail($subject, $body));

            return true;
        } catch (Throwable $e) {
            Log::error('Notification email failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Sends a real order/account notification over SMS via Fast2SMS's DLT
     * route — the only route that reliably delivers to DND-registered
     * numbers in India for non-OTP transactional content. Requires the
     * template to have a DLT-approved Template ID saved (set under
     * Notification Management > Message Templates > Edit Template); a
     * template with no ID configured is silently skipped, same as an
     * unconfigured email/push channel.
     */
    private function trySendTemplatedSms(string $phone, NotificationTemplate $template, array $vars): bool
    {
        if (! $template->sms_template_id) {
            return false;
        }

        $s = AppSetting::current();
        if (empty($s->fast2sms_api_key) || empty($s->fast2sms_sender_id)) {
            return false;
        }

        // variables_values must be pipe-separated, in the same order as the
        // {#var#} placeholders in the DLT-approved template text — that
        // order is exactly the template's own `variables` list, the same
        // list used to substitute {{var}} in the email/push body above.
        $values = collect($template->variables ?? [])
            ->map(fn ($key) => (string) ($vars[$key] ?? ''))
            ->implode('|');

        try {
            $response = Http::withHeaders([
                'Authorization' => $s->fast2sms_api_key,
                'Content-Type' => 'application/json',
            ])->post('https://www.fast2sms.com/dev/bulkV2', [
                'route' => 'dlt',
                'sender_id' => $s->fast2sms_sender_id,
                'message' => $template->sms_template_id,
                'variables_values' => $values,
                'numbers' => OtpService::normalizeIndianMobile($phone),
            ]);

            return $response->successful() && $response->json('return') === true;
        } catch (Throwable $e) {
            Log::error('Notification SMS failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Ad-hoc free-text SMS for admin broadcasts, where there's no
     * pre-approved DLT template to attach — Fast2SMS's "Quick SMS" route
     * (route=q) is the only option for arbitrary text, but TRAI blocks it
     * for most real (DND-registered) numbers, so broadcast SMS will often
     * fail even when this reports the request as accepted by Fast2SMS.
     */
    private function trySendSms(string $phone, string $message): bool
    {
        $s = AppSetting::current();
        if (empty($s->fast2sms_api_key)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $s->fast2sms_api_key])
                ->post('https://www.fast2sms.com/dev/bulkV2', [
                    'route' => 'q',
                    'message' => $message,
                    'language' => 'english',
                    'flash' => 0,
                    'numbers' => $phone,
                ]);

            return $response->successful() && $response->json('return') === true;
        } catch (Throwable $e) {
            Log::error('Notification SMS failed: '.$e->getMessage());

            return false;
        }
    }

    private function trySendPush(string $playerId, string $title, string $message): bool
    {
        $s = AppSetting::current();
        if (empty($s->onesignal_app_id) || empty($s->onesignal_api_key)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$s->onesignal_api_key,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $s->onesignal_app_id,
                'include_subscription_ids' => [$playerId],
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
            ]);

            return $response->successful() && ! $response->json('errors');
        } catch (Throwable $e) {
            Log::error('Notification push failed: '.$e->getMessage());

            return false;
        }
    }
}
