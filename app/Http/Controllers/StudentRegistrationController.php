<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'phone' => $request->phone,
            'bod' => $request->bod ?? now(),
            'username' => $request->username ?? $this->generateUniqueUsername($request->name),
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
            'device_access' => $request->deviceAccess,
            'tutoring_times' => $request->tutoringTime,
        ]);

        DB::commit();

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
}
