<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Module::when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->where('created_by', auth()->id())
            ->latest()->paginate($request->per_page ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModuleRequest $request)
    {
        try {
            DB::beginTransaction();

            $module = Module::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'created_by' => auth()->id(),
                'teacher_id' => auth()->id(),
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
        $module = Module::findOrFail($id);
        return $module;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $module = Module::findOrFail($id);

            if ($module->created_by != Auth::id() && !Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'You are not authorized to update this discussion'
                ], 403);
            }

            $module->update([
                'title' => $request->title ?? $module->title,
                'price' => $request->price ?? $module->price,
                'teacher_id' => auth()->id() ?? $module->teacher_id,
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
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
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
