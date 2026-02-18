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
use Illuminate\Support\Str;

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

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $newPassword = Str::password(16);

        $user->password = Hash::make($newPassword);
        $user->save();

        $appName = config('app.name', 'LMS');
        $name = $user->name ?? 'User';

        if (filter_var(env('MAILJET_LOG_ONLY', false), FILTER_VALIDATE_BOOLEAN)) {
            Log::info('MAILJET_LOG_ONLY: Password reset email (not sent)', [
                'to' => $user->email,
                'subject' => 'Your new password',
                'new_password' => $newPassword,
            ]);
        } else {
            $this->sendPasswordResetEmail($user, $newPassword);
        }

        return response()->json([
            'message' => 'A new password has been sent to your email. Please check your inbox and log in with it.',
        ]);
    }

    /**
     * Send password reset email with the new temporary password.
     */
    private function sendPasswordResetEmail(User $user, string $newPassword): void
    {
        $appName = config('app.name', 'LMS');
        $name = $user->name ?? 'User';

        Log::info('Sending password reset email with new password', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $htmlContent = view('emails.password-reset-new-password', [
            'name' => $name,
            'newPassword' => $newPassword,
        ])->render();

        $textContent = "Hello {$name},\n\n";
        $textContent .= "We received a request to reset your password. Your password has been reset. Use the temporary password below to log in:\n\n";
        $textContent .= "Your temporary password: {$newPassword}\n\n";
        $textContent .= "Log in with this password and change it to something you prefer (e.g. via Change Password in your account). Do not share this password with anyone.\n\n";
        $textContent .= "Best regards,\nThe {$appName} Team";

        $this->emailService->sendHtmlEmail(
            $user->email,
            'Your new password',
            $htmlContent,
            $textContent,
            $name
        );
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
}
