<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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

        $otp = random_int(100000, 999999);

        // Send OTP email
        try {
            $this->sendPasswordResetOTP($user, $otp);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Continue with OTP generation even if email fails
        }

        $encryptedOtp = Crypt::encryptString($otp);
        $user->otp = $encryptedOtp;
        $user->email_verified_at = null;
        $user->otp_sent_at = Carbon::now();
        $user->status = 1;
        $user->save();

        return response()->json([
            'message' => 'OTP sent successfully!',
        ]);
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
     * Test email endpoint - for debugging
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $testOtp = 123456;
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Force use Blade template
        $this->sendPasswordResetOTPWithBlade($user, $testOtp);

        return response()->json([
            'message' => 'Test email sent! Check logs for details.',
            'email' => $request->email,
            'otp' => $testOtp
        ]);
    }

    /**
     * Send password reset OTP email
     */
    private function sendPasswordResetOTP(User $user, int $otp)
    {
        // Always use Blade template for better reliability
        // Mailjet templates can have delivery issues if not properly configured
        Log::info('Sending password reset OTP email using Blade template', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        $this->sendPasswordResetOTPWithBlade($user, $otp);
    }

    /**
     * Send password reset OTP email using Blade template
     */
    private function sendPasswordResetOTPWithBlade(User $user, int $otp)
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');
        
        // Validate Mailjet credentials
        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials are missing for password reset OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        $mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);

        // Render Blade template for HTML email
        $htmlContent = view('emails.password-reset-otp', [
            'otp' => $otp,
            'name' => $user->name ?? 'User',
            'email' => $user->email,
        ])->render();

        // Create plain text version
        $textContent = "Hello {$user->name},\n\n";
        $textContent .= "We received a request to reset your password. Use the OTP code below to proceed:\n\n";
        $textContent .= "Your OTP Code: {$otp}\n";
        $textContent .= "This code will expire in 15 minutes\n\n";
        $textContent .= "Important:\n";
        $textContent .= "- This OTP is valid for 15 minutes only\n";
        $textContent .= "- Do not share this code with anyone\n";
        $textContent .= "- If you didn't request a password reset, please ignore this email\n\n";
        $textContent .= "If you have any questions or need assistance, please contact our support team.\n\n";
        $textContent .= "Best regards,\nThe LMS Team";

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                        'Name' => env('MAIL_FROM_NAME', env('APP_NAME', 'LMS')),
                    ],
                    'To' => [
                        [
                            'Email' => $user->email,
                            'Name' => $user->name ?? 'User',
                        ],
                    ],
                    'Subject' => 'Password Reset OTP',
                    'TextPart' => $textContent,
                    'HTMLPart' => $htmlContent,
                ],
            ],
        ];

        try {
            $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);
            
            if ($response->success()) {
                $responseData = $response->getData();
                Log::info('Password reset OTP email sent successfully via Mailjet (Blade template)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'message_id' => $responseData['Messages'][0]['To'][0]['MessageID'] ?? 'unknown',
                    'message_uuid' => $responseData['Messages'][0]['To'][0]['MessageUUID'] ?? 'unknown',
                    'status' => $responseData['Messages'][0]['Status'] ?? 'unknown'
                ]);
            } else {
                $responseData = $response->getData();
                Log::error('Failed to send password reset OTP email via Mailjet (Blade template)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'response' => $responseData,
                    'status_code' => $response->getStatus(),
                    'body' => $response->getBody()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending password reset OTP email via Mailjet', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
