<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class StudentRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'studentFullName' => 'required|string',
            'parentName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'country' => 'required|string',
            'gradeLevel' => 'required|string',
            'supportSubjects' => 'required|array',
            'learningGoals' => 'required|string',
            'tutoringTime' => 'required|string',
            'deviceAccess' => 'required|boolean',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:password',
            'consentTerms' => 'required|boolean',
            'consentTutoring' => 'required|boolean',
        ]);

        DB::beginTransaction();

        $user = User::create([
            'name' => $request->studentFullName,
            'email' => $request->email,
            'phone' => $request->phone ?: null,
            'bod' => $request->bod ?? now(),
            'username' => $request->username ?? $this->generateUniqueUsername($request->studentFullName),
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole('Student');


        Student::create([
            'user_id' => $user->id,
            'parent_name' => $request->parentName,
            'country' => $request->country,
            'grade_label' => $request->gradeLevel,
            'support_subjects' => json_encode($request->supportSubjects),
            'learning_goals' => $request->learningGoals,
            'device_access' => $request->boolean('deviceAccess'),
            'tutoring_times' => $request->tutoringTime,
        ]);

        DB::commit();
        
        // Send welcome email using Mailjet
        $this->sendWelcomeEmail($user);

        return response()->json(['message' => 'Registration successful'], 201);
    }

    public function generateUniqueUsername($name)
    {
        $username = Str::slug($name);
        $existingUser = User::where('username', $username)->first();

        if ($existingUser) {
            $username .= rand(1000, 9999);
        }

        while (User::where('username', $username)->exists()) {
            $username = Str::slug($name) . rand(1000, 1000);
        }

        return $username;
    }

    /**
     * Send welcome email to newly registered user using Mailjet
     */
    private function sendWelcomeEmail(User $user)
    {
        try {
            $emailService = new EmailService();
            
            // Get Mailjet template ID from env (optional)
            $templateId = env('MAILJET_WELCOME_TEMPLATE_ID', null);
            
            // Prepare email variables
            $variables = [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username ?? $user->email,
            ];

            // If template ID is set, use Mailjet template
            if ($templateId) {
                $emailService->sendEmail(
                    $user->email,
                    'Welcome to LMS - Your Learning Journey Begins!',
                    $templateId,
                    $variables
                );
            } else {
                // Fallback: Send simple email without template using Mailjet API
                $this->sendSimpleWelcomeEmail($user, $variables);
            }
        } catch (\Exception $e) {
            // Log error but don't fail registration
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send a simple welcome email without using Mailjet template
     */
    private function sendSimpleWelcomeEmail(User $user, array $variables)
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');
        
        // Validate Mailjet credentials
        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials are missing', [
                'user_id' => $user->id,
                'email' => $variables['email'],
            ]);
            return;
        }
        
        $mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);

        // Render Blade template for HTML email
        $htmlContent = view('emails.welcome', [
            'user' => $user,
            'name' => $variables['name'],
            'username' => $variables['username'],
        ])->render();

        // Create plain text version
        $textContent = "Dear {$variables['name']},\n\n";
        $textContent .= "Welcome to our Learning Management System! We're excited to have you join us.\n\n";
        $textContent .= "Your account has been successfully created. You can now start exploring our courses and learning materials.\n\n";
        $textContent .= "Account Details:\n";
        $textContent .= "Email: {$variables['email']}\n";
        $textContent .= "Username: {$variables['username']}\n\n";
        $textContent .= "If you have any questions, feel free to reach out to our support team.\n\n";
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
                            'Email' => $variables['email'],
                            'Name' => $variables['name'],
                        ],
                    ],
                    'Subject' => 'Welcome to LMS - Your Learning Journey Begins!',
                    'TextPart' => $textContent,
                    'HTMLPart' => $htmlContent,
                ],
            ],
        ];

        $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);
        
        if (!$response->success()) {
            Log::error('Failed to send welcome email via Mailjet', [
                'user_id' => $user->id,
                'email' => $variables['email'],
                'response' => $response->getData()
            ]);
        }
    }
}
