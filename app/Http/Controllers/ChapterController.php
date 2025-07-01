<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\ModuleTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Chapter::class);

        $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        $module = Module::find($request->module_id);

        return $module->chapters()->where('parent_id', null)->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->orderBy('order')
            ->paginate($request->per_page ?? 10);
    }

    public function myModuleChapters(Request $request, ModuleTeacher $moduleTeacher)
    {
        Gate::authorize('viewAny', Chapter::class);
        $module = $moduleTeacher->module;

        return $module->chapters()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->orderBy('order')
            ->paginate($request->per_page ?? 10);
    }

    public function all(Request $request)
    {
        Gate::authorize('viewAny', Chapter::class);

        $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        $module = Module::find($request->module_id);

        return $module->chapters()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->orderBy('order')
            ->get()->map(function ($chapter) {
                return [
                    'id' => $chapter->id,
                    'name' => $chapter->name,
                    'order' => $chapter->order,
                ];
            });
    }


    public function sortChapters(Request $request)
    {
        Gate::authorize('sortChapters', Chapter::class);

        $chapterCount = Chapter::whereModuleId($request->module_id)->count();


        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'chapters' => 'required|array|size:' . $chapterCount,
            'chapters.*' => [
                'required',
                Rule::exists('chapters', 'id')->where(function ($query) use ($request) {
                    $query->where('module_id', $request->module_id);
                }),
            ],
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->chapters as $index => $chapterId) {
                Chapter::where('id', $chapterId)->update([
                    'order' => $index + 1,
                ]);
            }

            DB::commit();

            return 'Chapters sorted successfully';
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request)
    {
        Gate::authorize('create', Chapter::class);
        try {
            DB::beginTransaction();

            if (isset($request->parent_id)) {
                $parent =  Chapter::find($request->parent_id);
                if ($parent->module_id !== $request->module_id) {
                    abort(403, "Parent chapter does not belong to the same module.");
                }
            }

            $chapter = Chapter::create([
                'module_id' => $request->module_id,
                'name' => $request->name,
                'description' => $request->description,
                'order' => Chapter::where('module_id', $request->module_id)->count() + 1,
                'is_custom' => $request->is_custom,
                'parent_id' => $request->parent_id,
            ]);

            if ($request->is_custom) {
                foreach ($request->student_ids as  $value) {
                    $chapter->studentContent()->create([
                        'student_id' => $value,
                    ]);
                }
            }
            DB::commit();

            return $chapter;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Chapter $chapter)
    {
        Gate::authorize('view', $chapter);
        return $chapter;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChapterRequest $request, Chapter $chapter)
    {
        Gate::authorize('update', $chapter);
        try {
            DB::beginTransaction();

            $chapter->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            return $chapter;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chapter $chapter)
    {
        Gate::authorize('delete', $chapter);
        try {
            DB::beginTransaction();

            // $chapter->delete();

            DB::commit();

            return 'Chapter deleted successfully';
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
