<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class OTPController extends BaseController
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    public function verify_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            abort(404, 'User not found!');
        }

        if ($user->email_verified_at != null) {
            abort(400, 'User already verified.');
        }

        // dd($user->otp);
        $decryptedOtp = Crypt::decryptString($user->otp);

        if ($request['otp'] == $decryptedOtp) {

            $to = Carbon::parse($user->otp_sent_at);

            if ($to->diffInMinutes(Carbon::now()) > 15) {
                abort(400, 'The code you entered has expired. Please resend code to verify your number.');
            }

            $user->update([
                'email_verified_at' => Carbon::now(),
            ]);

            $token = Auth::guard('api')->login($user);

            return response()
                ->json([
                    'message' => 'User verified successfully',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 201);
        } else {
            abort(400, 'Your otp is not correct!');
        }
    }

    public function resend_otp(Request $request)
    {
        $otp = random_int(100000, 999999);
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at !== null) {
            abort(400, 'User already verified!');
        }

        if (! empty($user)) {
            // Send OTP email
            try {
                $this->sendOTPEmail($user, $otp);
            } catch (\Exception $e) {
            }

            // Send SMS if phone is provided
            if ($request->has('phone')) {
                $this->sendSMS($request->phone, $otp, $request->appKey);
            }

            $encryptedOtp = Crypt::encryptString($otp);
            $user->update([
                'otp' => $encryptedOtp,
                'email_verified_at' => null,
                'otp_sent_at' => now(),
            ]);

            return response()->json([
                'message' => 'OTP sent successfully!',
            ]);
        } else {
            abort(404, 'User not found!');
        }
    }

    /**
     * Send OTP email
     */
    private function sendOTPEmail(User $user, int $otp)
    {
        // Get Mailjet template ID from env (optional, defaults to 6478497)
        $templateId = env('MAILJET_PASSWORD_RESET_TEMPLATE_ID') ?: 6478497;
        
        // Prepare email variables
        $variables = [
            'otp' => $otp,
            'name' => $user->name ?? 'User',
            'email' => $user->email,
        ];

        // Try to use Mailjet template if template ID is set
        if ($this->emailService->sendEmail(
            $user->email,
            'Password Reset OTP',
            $templateId,
            $variables
        )) {
            return; // Email sent successfully via template
        }

        // Fallback: Send email using Blade template
        $this->sendOTPEmailWithBlade($user, $otp);
    }

    /**
     * Send OTP email using Blade template
     */
    private function sendOTPEmailWithBlade(User $user, int $otp)
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');
        
        // Validate Mailjet credentials
        if (empty($apiKey) || empty($apiSecret)) {
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

        $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);
        
        if (!$response->success()) {
            return false;
        }
    }
}
