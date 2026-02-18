<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name', 'LMS') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff;">
        <h2 style="color: #4CAF50;">Welcome, {{ $name }}!</h2>
        <p>Thank you for registering as a student with {{ config('app.name', 'LMS') }}.</p>
        <p>Your account has been created successfully. You can now log in with your email and password to access your courses and learning materials.</p>
        <p><strong>What you can do next:</strong></p>
        <ul>
            <li>Log in to your account</li>
            <li>Explore available modules and enroll in courses</li>
            <li>Track your progress and grades</li>
        </ul>
        <p>If you have any questions or need assistance, please contact our support team.</p>
        <p>Best regards,<br><strong>The {{ config('app.name', 'LMS') }} Team</strong></p>
    </div>
</body>
</html>
