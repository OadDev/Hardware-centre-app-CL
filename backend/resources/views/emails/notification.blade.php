<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 0;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="background:#1F2937;padding:24px 32px;">
              <span style="color:#fff;font-size:18px;font-weight:700;">{{ config('app.name') }}</span>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <p style="margin:0;color:#111827;font-size:15px;line-height:24px;white-space:pre-line;">{{ $body }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
