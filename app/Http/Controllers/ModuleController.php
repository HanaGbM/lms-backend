<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Module;
use Illuminate\Http\Request;
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
                'created_by' => auth()->id(),
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
        return $module;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        try {
            DB::beginTransaction();

            $module->update([
                'title' => $request->title ?? $module->title,
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
        abort(403, 'Unauthorized');
        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }
}
