<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends BaseController
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)
            ->first();

        if (! $user) {
            abort(404, 'User not found!');
        }

        // Generate a random password (12 characters: letters, numbers, and special chars)
        $randomPassword = $this->generateRandomPassword();

        // Update user's password with the random password
        $user->password = Hash::make($randomPassword);
        $user->status = 1;
        $user->save();

        // Send password reset email (Blade email first - correct content; template 6478497 is for OTP)
        $emailSent = false;
        try {
            $emailSent = $this->sendPasswordResetEmail($user, $randomPassword);
            if (!$emailSent) {
                $templateId = (int) (env('MAILJET_PASSWORD_RESET_TEMPLATE_ID') ?: 6478497);
                Log::warning('Password reset Blade email failed, trying Mailjet template', [
                    'email' => $request->email,
                    'template_id' => $templateId,
                ]);
                $emailSent = $this->emailService->sendEmail(
                    $request->email,
                    'Your New Password - Password Reset',
                    $templateId,
                    [
                        'password' => $randomPassword,
                        'name' => $user->name ?? 'User',
                    ]
                );
            }
            if ($emailSent) {
                Log::info('Password reset email sent successfully', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                ]);
            } else {
                Log::error('Password reset email could not be sent (all methods failed)', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending password reset email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
        }

        if ($emailSent) {
            return response()->json([
                'message' => 'New password sent to your email! Please check your inbox (and spam folder) and login with the new password.',
            ]);
        }

        return response()->json([
            'message' => 'Your password was reset, but we could not send the email. Please check that MAILJET_* and MAIL_FROM_* are set in .env, that your Mailjet sender is verified, and check storage/logs/laravel.log for details. For local testing, set MAILJET_LOG_ONLY=true to see the new password in the log.',
        ], 503);
    }

    /**
     * Generate a random secure password
     */
    private function generateRandomPassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }
        
        return $password;
    }

    public function create_password(ResetPasswordRequest $request)
    {
        $user = User::where('id', Auth::id())->first();
        $user->update(['password' => Hash::make($request->new_password)]);

        return 'Password changed successfully!';
    }

    public function change_password(ChangePasswordRequest $request)
    {
        User::find(Auth::id())
            ->update(['password' => Hash::make($request->new_password), 'status' => 0]);

        return User::find(Auth::id());
    }

    /**
     * Send password reset email with new password (Blade content via Mailjet API).
     * Returns true if sent (or logged when MAILJET_LOG_ONLY), false otherwise.
     */
    private function sendPasswordResetEmail(User $user, string $password): bool
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');

        // Local/dev: log email and skip Mailjet so you can see the password in storage/logs/laravel.log
        if (filter_var(env('MAILJET_LOG_ONLY', false), FILTER_VALIDATE_BOOLEAN)) {
            Log::info('MAILJET_LOG_ONLY: Password reset email (not sent)', [
                'to' => $user->email,
                'subject' => 'Your New Password - Password Reset',
                'plain_text' => "Your Temporary Password: {$password}\n\nLogin with this password then change it in profile settings.",
            ]);
            return true;
        }

        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials missing for password reset email');
            return false;
        }

        $mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $mj->setConnectionTimeout((int) env('MAILJET_CONNECT_TIMEOUT', 10));
        $mj->setTimeout((int) env('MAILJET_TIMEOUT', 30));

        $htmlContent = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 8px;'>
                <h2 style='color: #4CAF50;'>Password Reset - Your New Password</h2>
                <p>Hello {$user->name},</p>
                <p>We received a request to reset your password. Your account password has been reset. Use the temporary password below to login:</p>
                <div style='background-color: #f8f9fa; border: 2px dashed #4CAF50; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;'>
                    <p style='margin: 0; font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px;'>Your Temporary Password</p>
                    <h1 style='margin: 10px 0; font-size: 24px; color: #4CAF50; font-family: monospace; word-break: break-all;'>{$password}</h1>
                </div>
                <p><strong>What to do next:</strong></p>
                <ol>
                    <li>Login to your account using this temporary password</li>
                    <li>Go to your profile settings</li>
                    <li>Change your password to something you'll remember</li>
                </ol>
                <p><strong>Important:</strong></p>
                <ul>
                    <li>Do not share this password with anyone</li>
                    <li>Change your password immediately after logging in</li>
                    <li>If you didn't request this, please contact support immediately</li>
                </ul>
                <p>Best regards,<br><strong>The LMS Team</strong></p>
            </div>
        </body>
        </html>
        ";

        $textContent = "Hello {$user->name},\n\n";
        $textContent .= "We received a request to reset your password. Your account password has been reset.\n\n";
        $textContent .= "Your Temporary Password: {$password}\n\n";
        $textContent .= "What to do next:\n";
        $textContent .= "1. Login to your account using this temporary password\n";
        $textContent .= "2. Go to your profile settings\n";
        $textContent .= "3. Change your password to something you'll remember\n\n";
        $textContent .= "Important:\n";
        $textContent .= "- Do not share this password with anyone\n";
        $textContent .= "- Change your password immediately after logging in\n";
        $textContent .= "- If you didn't request this, please contact support immediately\n\n";
        $textContent .= "Best regards,\nThe LMS Team";

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('MAIL_FROM_ADDRESS', 'admin@amanueld.info'),
                        'Name' => env('MAIL_FROM_NAME', 'LMS'),
                    ],
                    'To' => [
                        [
                            'Email' => $user->email,
                            'Name' => $user->name ?? 'User',
                        ],
                    ],
                    'Subject' => 'Your New Password - Password Reset',
                    'TextPart' => $textContent,
                    'HTMLPart' => $htmlContent,
                ],
            ],
        ];

        try {
            $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                Log::info('Password reset email sent successfully', [
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);
                return true;
            }

            Log::error('Failed to send password reset email', [
                'email' => $user->email,
                'response' => $response->getData(),
                'status' => $response->getStatus(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending password reset email', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
