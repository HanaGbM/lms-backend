<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\StudentModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{
    public function modules(Request $request)
    {
        Gate::authorize('viewAny', Module::class);

        return ModuleTeacher::latest()->paginate($request->per_page ?? 10)->through(function ($moduleTeacher) {
            $module = $moduleTeacher->module;
            $module->is_enrolled = $module->students()->where('student_id', auth()->id())->exists();
            return [
                'id' => $moduleTeacher->id,
                'module_id' => $moduleTeacher->module_id,
                'title' => $module->title,
                'description' => $module->description,
                'cover' => $module->cover,
                'is_enrolled' => $module->is_enrolled,
                'teacher' => $moduleTeacher->teacher->name,
            ];
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

                $module = $studentModule->moduleTeacher->module;

                return [
                    'id' => $studentModule->id,
                    'module_id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'cover' => $module->cover,
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
        Gate::authorize('viewAnyStudents', User::class);
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
        })->latest()->paginate($request->per_page ?? 10);
    }
}
