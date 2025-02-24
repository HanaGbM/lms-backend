<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminModuleRequest;
use App\Http\Requests\UpdateAdminModuleRequest;
use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\StudentModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Module::when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminModuleRequest $request)
    {
        try {
            DB::beginTransaction();

            $module = Module::create([
                'title' => $request->title,
                'description' => $request->description,
                'created_by' => auth()->id(),
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
    public function show($id)
    {
        $module = Module::with('teacherModules.teacher')->findOrFail($id);

        $teacherModules = $module->teacherModules->map(function ($teacherModule) {
            $paginatedStudents = $teacherModule->students()->paginate(10);
            $teacherModule->setRelation('students', $paginatedStudents);
            return $teacherModule;
        });

        $module->setRelation('teacherModules', $teacherModules);

        return $module;
    }

    public function assignTeachers(Request $request, $id)
    {
        $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|exists:users,id|distinct',
        ]);

        $module = Module::findOrFail($id);

        foreach ($request->teacher_ids as $key => $value) {
            ModuleTeacher::updateOrCreate([
                'module_id' => $module->id,
                'teacher_id' => $value,
            ]);
        }

        return response()->json(['message' => 'Teachers assigned successfully']);
    }

    public function assignStudents(Request $request, $id)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|exists:users,id|distinct',
        ]);

        foreach ($request->student_ids as  $value) {
            StudentModule::updateOrCreate([
                'student_id' => $value,
                'module_teacher_id' => $id,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json(['message' => 'Students assigned successfully']);
    }

    public function getModuleTeachers(Request $request, $id)
    {
        return ModuleTeacher::where('module_id', $id)
            ->paginate(10)->through(function ($moduleTeacher) {
                return [
                    'id' => $moduleTeacher->id,
                    'name' => $moduleTeacher->teacher->name,
                    'username' => $moduleTeacher->teacher->username,
                    'phone' => $moduleTeacher->teacher->phone,
                    'email' => $moduleTeacher->teacher->email,
                    'bod' => $moduleTeacher->teacher->bod,
                    'assigned_at' => $moduleTeacher->created_at,
                    'profile_photo_url' => $moduleTeacher->teacher->profile_photo_url,
                ];
            });
    }

    public function getModuleStudents(Request $request, $id)
    {
        return ModuleTeacher::findOrFail($id)
            ->students()->paginate(10);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminModuleRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $module = Module::findOrFail($id);

            $module->update([
                'title' => $request->title ?? $module->title,
                'description' => $request->description ?? $module->description,
                'price' => $request->price ?? $module->price,
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
    public function destroy($id)
    {
        $module = Module::findOrFail($id);

        abort(403, 'Unauthorized');
        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }
}
