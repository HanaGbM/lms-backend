<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\StudentModule;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function modules(Request $request)
    {
        return Module::when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10)->through(function ($module) {
            $module->is_enrolled = $module->students()->where('student_id', auth()->id())->exists();
            return $module;
        });
    }

    public function myModules(Request $request)
    {
        return StudentModule::where('student_id', auth()->id())
            ->when($request->has('search'), function ($query) use ($request) {
                $query->whereHas('module', function ($query) use ($request) {
                    $query->where('title', 'like', "%{$request->search}%");
                });
            })->latest()->paginate($request->per_page ?? 10)->through(function ($studentModule) {
                $studentModule->module->is_enrolled = true;
                return [
                    'id' => $studentModule->id,
                    'module_id' => $studentModule->module_id,
                    'title' => $studentModule->module->title,
                    'description' => $studentModule->module->description,
                    'cover' => $studentModule->module->cover,
                    'status' => $studentModule->status,
                    'enrolled_at' => $studentModule->created_at,
                    'started_at' => $studentModule->started_at,
                    'completed_at' => $studentModule->completed_at,
                ];
            });
    }

    public function moduleDetail(StudentModule $studentModule)
    {
        return [
            'id' => $studentModule->id,
            'title' => $studentModule->module->title,
            'description' => $studentModule->module->description,
            'cover' => $studentModule->module->cover,
            'status' => $studentModule->status,
            'enrolled_at' => $studentModule->created_at,
            'started_at' => $studentModule->started_at,
            'completed_at' => $studentModule->completed_at,
        ];
    }

    public function students(Request $request)
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
            $query->where('name', 'Student');
        })->latest()
            ->latest()->paginate($request->per_page ?? 10);
    }
}
