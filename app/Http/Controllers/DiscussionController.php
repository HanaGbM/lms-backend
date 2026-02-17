<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscussionRequest;
use App\Http\Requests\UpdateDiscussionRequest;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\ModuleTeacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ModuleTeacher $moduleTeacher)
    {
        Gate::authorize('viewAny', Discussion::class);

        return $moduleTeacher->discussions()->latest()->paginate(10)->through(function ($discussion) {
            $discussion->replies = $discussion->replies()->latest()->paginate(10);
            return $discussion->load('user');
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscussionRequest $request, ModuleTeacher $moduleTeacher)
    {
        Gate::authorize('create', Discussion::class);
        try {

            DB::beginTransaction();

            $discussion = $moduleTeacher->discussions()->create([
                'title' => $request->title,
                'content' => $request->content,
                'user_id' => auth()->id(),
            ]);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $discussion->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();

            return response()->json([
                'message' => 'Discussion created successfully',
                'discussion' => $discussion
            ], 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Discussion $discussion)
    {
        Gate::authorize('view', $discussion);

        $discussion->replies = $discussion->replies()->latest()->paginate(10);
        return $discussion->load('user');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscussionRequest $request, Discussion $discussion)
    {
        Gate::authorize('update', $discussion);
        try {
            DB::beginTransaction();

            if ($discussion->user_id != Auth::id()) {
                return response()->json([
                    'message' => 'You are not authorized to update this discussion'
                ], 403);
            }

            $discussion->update([
                'title' => $request->title ?? $discussion->title,
                'content' => $request->content ?? $discussion->content,
            ]);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $discussion->clearMediaCollection('image');
                $discussion->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();

            return response()->json([
                'message' => 'Discussion updated successfully',
                'discussion' => $discussion
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discussion $discussion)
    {
        Gate::authorize('delete', $discussion);
        if ($discussion->user_id != Auth::id()) {
            return response()->json([
                'message' => 'You are not authorized to update this discussion'
            ], 403);
        }

        $discussion->delete();

        return response()->json([
            'message' => 'Discussion deleted successfully'
        ]);
    }
}
