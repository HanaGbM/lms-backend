<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminModuleRequest;
use App\Http\Requests\UpdateAdminModuleRequest;
use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\StudentModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminModuleController extends Controller
{
    public function assignTeachers(Request $request, Module $module)
    {
        Gate::authorize('assignTeachers', $module);
        $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|exists:users,id|distinct',
        ]);

        foreach ($request->teacher_ids as  $value) {
            ModuleTeacher::updateOrCreate([
                'module_id' => $module->id,
                'teacher_id' => $value,
            ]);
        }

        return response()->json(['message' => 'Teachers assigned successfully']);
    }

    public function assignStudents(Request $request, $id)
    {
        Gate::authorize('assignStudents', Module::class);

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|exists:users,id|distinct',
        ]);

        $moduleTeacher = ModuleTeacher::findOrFail($id);

        foreach ($request->student_ids as  $value) {
            StudentModule::updateOrCreate([
                'student_id' => $value,
                'module_teacher_id' => $moduleTeacher->id,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json(['message' => 'Students assigned successfully']);
    }

    public function getModuleTeachers(Request $request, $id)
    {
        Gate::authorize('viewModuleTeachers', Module::class);
        return ModuleTeacher::where('module_id', $id)
            ->paginate(10)->through(function ($moduleTeacher) {
                return [
                    'id' => $moduleTeacher->id,
                    'teacher_id' => $moduleTeacher->teacher_id,
                    'name' => $moduleTeacher->teacher->name,
                    'module' => [
                        'id' => $moduleTeacher->module->id,
                        'title' => $moduleTeacher->module->title,
                        'description' => $moduleTeacher->module->description,
                        'cover_url' => $moduleTeacher->module->getFirstMediaUrl('cover'),
                    ],
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
        Gate::authorize('viewModuleStudents', Module::class);

        return ModuleTeacher::findOrFail($id)
            ->students()
            ->paginate(10);
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
