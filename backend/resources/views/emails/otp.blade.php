<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 0;">
    <tr>
      <td align="center">
        <table width="420" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="background:#1F2937;padding:24px 32px;">
              <span style="color:#fff;font-size:18px;font-weight:700;">{{ config('app.name') }}</span>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 16px;color:#111827;font-size:15px;">Your verification code is:</p>
              <p style="margin:0 0 16px;font-size:32px;font-weight:700;letter-spacing:6px;color:#F97316;">{{ $code }}</p>
              <p style="margin:0;color:#6B7280;font-size:13px;">This code expires in 5 minutes. If you didn't request this, you can ignore this email.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
