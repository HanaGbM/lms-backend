<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function teachers(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer',
            'search' => 'nullable|string',
        ]);

        return User::when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%")
                ->where(function ($query) use ($request) {
                    $query->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
        })->whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->latest()->paginate($request->per_page ?? 10);
    }
}
