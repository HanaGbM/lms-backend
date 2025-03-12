<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestRequest;
use App\Http\Requests\UpdateTestRequest;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Test::class);

        $request->validate([
            'model_type' => 'required|in:module,chapter',
            'module_id' => 'required_if:category,module|exists:modules,id',
            'chapter_id' => 'required_if:category,chapter|exists:chapters,id',
        ]);

        if ($request->model_type === 'module') {
            $module = Module::find($request->module_id);
            $tests = $module->tests()->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })->get();
        } elseif ($request->model_type === 'chapter') {
            $chapter = Chapter::find($request->chapter_id);
            $tests = $chapter->tests()->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })->get();
        } else {
            $tests = [];
        }

        return $tests;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTestRequest $request)
    {
        DB::beginTransaction();

        Gate::authorize('create', Test::class);

        if ($request->model_type === 'module') {
            $module = Module::find($request->module_id);

            $exists = $module->tests()->where('name', $request->name)->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Test with this name already exists',
                ], 400);
            }

            $test =   $module->tests()->create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'duration' => $request->duration ?? 0,
                'duration_unit' => $request->duration_unit ?? 'minutes',
            ]);
        } elseif ($request->model_type === 'chapter') {
            $chapter = Chapter::find($request->chapter_id);

            $exists = $chapter->tests()->where('name', $request->name)->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Test with this name already exists',
                ], 400);
            }

            $test =  $chapter->tests()->create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'duration' => $request->duration,
                'duration_unit' => $request->duration_unit,
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid model type',
            ], 400);
        }

        DB::commit();

        return response()->json([
            'message' => 'Test created successfully',
            'test' => $test,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Test $test)
    {
        Gate::authorize('view', $test);

        return $test;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestRequest $request, Test $test)
    {
        DB::beginTransaction();

        Gate::authorize('update', $test);

        $test->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'duration' => $request->duration ?? 0,
            'duration_unit' => $request->duration_unit ?? 'minutes',
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Test updated successfully',
            'test' => $test,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Test $test)
    {
        //
    }
}
