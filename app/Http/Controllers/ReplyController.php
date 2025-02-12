<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Http\Requests\UpdateReplyRequest;
use App\Models\Discussion;
use App\Models\Reply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Discussion $discussion)
    {
        return $discussion->replies()->latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReplyRequest $request, Discussion $discussion)
    {
        try {
            try {

                DB::beginTransaction();

                $reply = $discussion->replies()->create([
                    'content' => $request->content,
                    'user_id' => auth()->id(),
                ]);

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $reply->addMediaFromRequest('image')->toMediaCollection('image');
                }

                DB::commit();

                return response()->json([
                    'message' => 'Reply created successfully',
                    'reply' => $reply
                ], 201);
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reply $reply)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        try {
            try {

                DB::beginTransaction();

                if ($reply->user_id != Auth::id()) {
                    return response()->json([
                        'message' => 'You are not authorized to update this discussion'
                    ], 403);
                }

                $reply->update([
                    'content' => $request->content ?? $reply->content,
                ]);

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $reply->clearMediaCollection('image');
                    $reply->addMediaFromRequest('image')->toMediaCollection('image');
                }

                DB::commit();

                return response()->json([
                    'message' => 'Reply updated successfully',
                    'reply' => $reply
                ], 200);
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        try {
            try {

                DB::beginTransaction();

                if ($reply->user_id != Auth::id()) {
                    return response()->json([
                        'message' => 'You are not authorized to update this discussion'
                    ], 403);
                }

                $reply->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Reply deleted successfully'
                ], 200);
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
