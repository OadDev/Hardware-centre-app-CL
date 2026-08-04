<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Sends and verifies OTP codes via email (SMTP) or SMS (Fast2SMS), whichever
 * the caller asks for. Settings > OTP Verification only picks the *default*
 * channel shown first on the login screen — the app always lets a customer
 * switch to the other channel instead, so an account created under one
 * channel is never locked out if the admin later changes the default.
 *
 * Two fixed test accounts (see TEST_CONTACTS) always accept the code
 * TEST_OTP_CODE without ever actually sending anything, so the app can be
 * logged into and reviewed (app store review, QA, demoing) even before real
 * SMTP/Fast2SMS credentials are configured. They don't affect delivery or
 * verification for any other contact.
 */
class OtpService
{
    private const TEST_CONTACTS = ['9999999999', 'test@hardwarecentre.in'];

    private const TEST_OTP_CODE = '123456';

    /**
     * The channel shown by default on the login screen. Not a hard gate —
     * send()/verify() work for either channel regardless of this setting.
     */
    public function activeChannel(): string
    {
        return AppSetting::current()->otp_channel === 'sms' ? 'sms' : 'email';
    }

    /**
     * Generates, stores, and dispatches a code to $contact over $channel.
     * Returns the plain code so the caller can decide whether to surface it
     * (e.g. in local dev).
     */
    public function send(string $contact, string $channel): string
    {
        if ($this->isTestContact($contact)) {
            return self::TEST_OTP_CODE;
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($channel, $contact), Hash::make($code), now()->addMinutes(5));

        if ($channel === 'sms') {
            $this->sendSms($contact, $code);
        } else {
            $this->sendEmail($contact, $code);
        }

        return $code;
    }

    public function verify(string $contact, string $code, string $channel): bool
    {
        if ($this->isTestContact($contact)) {
            return $code === self::TEST_OTP_CODE;
        }

        $key = $this->cacheKey($channel, $contact);
        $hashed = Cache::get($key);

        if (! $hashed || ! Hash::check($code, $hashed)) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function isTestContact(string $contact): bool
    {
        return in_array(strtolower($contact), self::TEST_CONTACTS, true);
    }

    protected function cacheKey(string $channel, string $contact): string
    {
        return "otp:{$channel}:{$contact}";
    }

    protected function sendEmail(string $email, string $code): void
    {
        $s = AppSetting::current();

        if (empty($s->smtp_host)) {
            throw new RuntimeException('SMTP is not configured. Set it up under Settings > OTP Verification.');
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $s->smtp_host,
            'mail.mailers.smtp.port' => $s->smtp_port ?: 587,
            'mail.mailers.smtp.username' => $s->smtp_username,
            'mail.mailers.smtp.password' => $s->smtp_password,
            'mail.mailers.smtp.encryption' => $s->smtp_encryption ?: 'tls',
            'mail.from.address' => $s->smtp_from_address ?: ($s->support_email ?: 'no-reply@'.parse_url(config('app.url'), PHP_URL_HOST)),
            'mail.from.name' => $s->smtp_from_name ?: ($s->business_name ?: config('app.name')),
        ]);

        Mail::to($email)->send(new OtpMail($code));
    }

    protected function sendSms(string $phone, string $code): void
    {
        $s = AppSetting::current();

        if (empty($s->fast2sms_api_key)) {
            throw new RuntimeException('Fast2SMS is not configured. Set it up under Settings > OTP Verification.');
        }

        $response = Http::asForm()
            ->withHeaders(['authorization' => $s->fast2sms_api_key])
            ->post('https://www.fast2sms.com/dev/bulkV2', [
                'route' => 'otp',
                'variables_values' => $code,
                'numbers' => $phone,
            ]);

        if (! $response->successful() || $response->json('return') === false) {
            throw new RuntimeException('Fast2SMS request failed: '.$response->body());
        }
    }
}
