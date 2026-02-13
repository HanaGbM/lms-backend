<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #4CAF50; margin-top: 0;">Password Reset Request</h2>
        
        <p>Hello {{ $name }},</p>
        
        <p>We received a request to reset your password. Use the OTP code below to proceed:</p>
        
        <div style="background-color: #f8f9fa; border: 2px dashed #4CAF50; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <p style="margin: 0; font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px;">Your OTP Code</p>
            <h1 style="margin: 10px 0; font-size: 36px; color: #4CAF50; letter-spacing: 5px; font-weight: bold;">{{ $otp }}</h1>
            <p style="margin: 0; font-size: 12px; color: #999;">This code will expire in 15 minutes</p>
        </div>
        
        <p><strong>Important:</strong></p>
        <ul style="padding-left: 20px;">
            <li>This OTP is valid for 15 minutes only</li>
            <li>Do not share this code with anyone</li>
            <li>If you didn't request a password reset, please ignore this email</li>
        </ul>
        
        <p>If you have any questions or need assistance, please contact our support team.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>The LMS Team</strong>
        </p>
    </div>
</body>
</html>
