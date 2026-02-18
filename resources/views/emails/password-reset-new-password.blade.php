<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your New Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff;">
        <h2 style="color: #4CAF50;">Password Reset – Your New Password</h2>
        <p>Hello {{ $name }},</p>
        <p>We received a request to reset your password. Your password has been reset. Use the temporary password below to log in:</p>
        <div style="background-color: #f8f9fa; border: 2px dashed #4CAF50; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <p style="margin: 0; font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px;">Your temporary password</p>
            <p style="margin: 10px 0; font-size: 18px; color: #4CAF50; font-family: monospace; letter-spacing: 2px; word-break: break-all;">{{ $newPassword }}</p>
        </div>
        <p><strong>Important:</strong></p>
        <ul>
            <li>Log in with this password and change it to something you prefer (e.g. via Change Password in your account)</li>
            <li>Do not share this password with anyone</li>
            <li>If you didn't request a password reset, please contact support immediately</li>
        </ul>
        <p>Best regards,<br><strong>The {{ config('app.name', 'LMS') }} Team</strong></p>
    </div>
</body>
</html>
