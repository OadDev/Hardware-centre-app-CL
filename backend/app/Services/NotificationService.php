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
 * and configured (Email via SMTP, SMS via Fast2SMS). A channel that's
 * toggled on but has no working credentials is silently skipped rather than
 * failing the caller's underlying action (an order still gets placed even
 * if its confirmation email can't be sent).
 */
class NotificationService
{
    public function notify(string $templateKey, ?string $email, ?string $phone, array $vars = []): void
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
            $this->trySendSms($phone, $body);
        }
    }

    /**
     * Ad-hoc broadcast to many recipients at once (Notification Management >
     * New Broadcast). Returns a per-channel delivery count instead of
     * silently claiming success for channels that were skipped.
     *
     * @param  iterable<array{email?: ?string, phone?: ?string}>  $recipients
     */
    public function broadcast(string $subject, string $message, iterable $recipients): array
    {
        $emailOn = $this->channelEnabled('email');
        $smsOn = $this->channelEnabled('sms');
        $summary = ['email_sent' => 0, 'email_failed' => 0, 'sms_sent' => 0, 'sms_failed' => 0, 'email_enabled' => $emailOn, 'sms_enabled' => $smsOn];

        foreach ($recipients as $r) {
            if ($emailOn && ! empty($r['email'])) {
                $this->trySendEmail($r['email'], $subject, $message) ? $summary['email_sent']++ : $summary['email_failed']++;
            }
            if ($smsOn && ! empty($r['phone'])) {
                $this->trySendSms($r['phone'], $message) ? $summary['sms_sent']++ : $summary['sms_failed']++;
            }
        }

        return $summary;
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

    private function trySendSms(string $phone, string $message): bool
    {
        $s = AppSetting::current();
        if (empty($s->fast2sms_api_key)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['authorization' => $s->fast2sms_api_key])
                ->post('https://www.fast2sms.com/dev/bulkV2', [
                    'route' => 'q',
                    'message' => $message,
                    'language' => 'english',
                    'flash' => 0,
                    'numbers' => $phone,
                ]);

            return $response->successful() && $response->json('return') !== false;
        } catch (Throwable $e) {
            Log::error('Notification SMS failed: '.$e->getMessage());

            return false;
        }
    }
}
