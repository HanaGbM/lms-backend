<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterMaterialRequest;
use App\Http\Requests\UpdateChapterMaterialRequest;
use App\Models\Chapter;
use App\Models\ChapterMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ChapterMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $chapter = Chapter::find($request->chapter_id);

        return $chapter->materials()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterMaterialRequest $request)
    {
        try {
            DB::beginTransaction();

            $chapterMaterial = ChapterMaterial::create([
                'chapter_id' => $request->chapter_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $chapterMaterial->addMediaFromRequest('file')
                    ->toMediaCollection('file');
            }

            DB::commit();

            return $chapterMaterial;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChapterMaterial $chapterMaterial)
    {
        return $chapterMaterial;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChapterMaterialRequest $request, ChapterMaterial $chapterMaterial)
    {
        try {
            DB::beginTransaction();

            $chapterMaterial->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $chapterMaterial->addMediaFromRequest('file')
                    ->toMediaCollection('file');
            }

            DB::commit();

            return $chapterMaterial;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChapterMaterial $chapterMaterial)
    {
        try {
            DB::beginTransaction();

            $chapterMaterial->delete();

            DB::commit();

            return response()->json(['message' => 'Chapter material deleted successfully']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteFile($id)
    {
        try {
            DB::beginTransaction();

            Media::where('uuid', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'File deleted successfully']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
