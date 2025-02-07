<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        $module = Module::find($request->module_id);

        return $module->courses()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        try {
            DB::beginTransaction();

            $course = Course::create([
                'module_id' => $request->module_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $course->addMedia($request->file('file'))->toMediaCollection('file');
            }

            DB::commit();

            return $course;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
    }
}
