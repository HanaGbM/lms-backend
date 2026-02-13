<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LMS</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #4CAF50; margin-top: 0;">Welcome to LMS, {{ $name }}!</h2>
        
        <p>We're thrilled to have you join our learning community!</p>
        
        <p>Your account has been successfully created. You can now:</p>
        
        <ul style="padding-left: 20px;">
            <li>Explore our courses and modules</li>
            <li>Access learning materials</li>
            <li>Track your progress</li>
            <li>Connect with teachers and fellow students</li>
        </ul>
        
        <p><strong>Your Account Details:</strong></p>
        <ul style="padding-left: 20px;">
            <li>Email: {{ $user->email }}</li>
            <li>Username: {{ $username }}</li>
        </ul>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>The LMS Team</strong>
        </p>
    </div>
</body>
</html>
