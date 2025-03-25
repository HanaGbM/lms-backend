<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Module;
use App\Models\ModuleTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        Gate::authorize('viewAny', Module::class);
        return Module::when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10);
    }


    public function myModules(Request $request)
    {
        return ModuleTeacher::latest()
            ->when($request->has('search'), function ($query) use ($request) {
                $query->whereHas('module', function ($query) use ($request) {
                    $query->where('title', 'like', "%{$request->search}%");
                });
            })->where('teacher_id', Auth::id())
            ->paginate($request->per_page ?? 10)->through(function ($moduleTeacher) {
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModuleRequest $request)
    {
        Gate::authorize('create', Module::class);
        try {
            DB::beginTransaction();

            $module = Module::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
            ]);

            if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
                $module->addMediaFromRequest('cover')
                    ->toMediaCollection('cover');
            }

            DB::commit();

            return response()->json(['message' => 'Module created successfully'], 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        Gate::authorize('view', $module);

        $teacherModules = $module->teacherModules->map(function ($teacherModule) {
            $paginatedStudents = $teacherModule->students()->paginate(10);
            $teacherModule->setRelation('students', $paginatedStudents);
            return $teacherModule;
        });

        $module->setRelation('teacherModules', $teacherModules);
        return $module;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        Gate::authorize('update', $module);
        try {
            DB::beginTransaction();

            if ($module->created_by != Auth::id() && !Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'You are not authorized to update this discussion'
                ], 403);
            }

            $module->update([
                'title' => $request->title ?? $module->title,
                'price' => $request->price ?? $module->price,
                'description' => $request->description ?? $module->description,
            ]);

            if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
                $module->clearMediaCollection('cover');
                $module->addMediaFromRequest('cover')
                    ->toMediaCollection('cover');
            }

            DB::commit();

            return response()->json(['message' => 'Module updated successfully']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        Gate::authorize('delete', $module);
        if ($module->created_by != Auth::id() && !Auth::user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'You are not authorized to update this discussion'
            ], 403);
        }

        abort(403, 'Unauthorized');
        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }
}
