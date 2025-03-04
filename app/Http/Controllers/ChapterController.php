<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
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

        return $module->chapters()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request)
    {
        try {
            DB::beginTransaction();

            $chapter = Chapter::create([
                'module_id' => $request->module_id,
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
     * Display the specified resource.
     */
    public function show(Chapter $chapter)
    {
        return $chapter;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChapterRequest $request, Chapter $chapter)
    {
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
