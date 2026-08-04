<?php

namespace App\Support;

use App\Models\AppSetting;

class SmtpMailer
{
    /**
     * Points Laravel's mail config at the admin-configured SMTP server.
     * Returns false (leaving config untouched) if SMTP hasn't been set up
     * yet under Settings > Security & SMTP.
     */
    public static function configure(): bool
    {
        $s = AppSetting::current();

        if (empty($s->smtp_host)) {
            return false;
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

        return true;
    }
}
