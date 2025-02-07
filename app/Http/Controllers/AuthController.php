<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePasswordRequest;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register($validated)
    {
        $user = new User($validated);
        $user->password = Hash::make($validated['password']);
        $user->username = $validated['username'] ?? $this->generateUniqueUsername($validated['name']);
        $user->save();

        return $user;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->orWhere('phone', $request->username)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $credentials = [
            'password' => $request->password,
        ];

        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $request->username;
        } elseif (is_numeric($request->username)) {
            $credentials['phone'] = $request->username;
        } else {
            $credentials['username'] = $request->username;
        }

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(
                ['message' => 'Invalid Credentials'],
                400
            );
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return User::find(Auth::id())->load('roles');
    }

    // update_profile
    public function update_profile(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string',
            'phone' => 'nullable|unique:users,phone,' . Auth::id(),
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'username' => 'nullable|string|unique:users,username,' . Auth::id(),
        ]);
        $user = User::find(Auth::id());

        if (! empty($user)) {
            $user->update([
                'name' => $request->name ?? $user->name,
                'email' => $request->email ?? $user->email,
                'phone' => $request->phone ?? $user->phone,
            ]);

            return User::find(Auth::id());
        } else {
            abort(404, 'Invalid Token.');
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    public function create_password(CreatePasswordRequest $request)
    {
        $user = User::find(Auth::id());

        if ($user->status === 0) {
            abort(400, 'Password already created!');
        }
        if (! empty($user)) {
            $user->update([
                'password' => Hash::make($request->password),
                'status' => 0,
            ]);

            return User::find(Auth::id());
        } else {
            abort(404, 'User not found!');
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = User::find(Auth::id());

        return response()->json([
            'access_token' => $token,
            'user' => $user->load('roles'),
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
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

    // change profile image
    public function update_profile_image(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|mimes:png,jpeg,jpg,svg,gif,bmp,bmp,tiff,webp',
        ]);

        $user = User::find(Auth::id());

        if (! empty($user)) {

            if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
                $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
            }

            return User::find(Auth::id());
        } else {
            abort(404, 'Invalid Token.');
        }
    }

    // remove profile image
    public function remove_profile_image()
    {
        $user = User::find(Auth::id());

        if (! empty($user)) {
            $user->clearMediaCollection('profile_image');

            return User::find(Auth::id());
        } else {
            abort(404, 'Invalid Token.');
        }
    }
}
