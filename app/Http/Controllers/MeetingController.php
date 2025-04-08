<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Meeting::class);

        return Meeting::latest()->paginate();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMeetingRequest $request)
    {
        Gate::authorize('create', Meeting::class);
        try {
            DB::beginTransaction();

            $meeting = Meeting::create([
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->participants as $value) {
                $meeting->invites()->create([
                    'user_id' => $value,
                    'status' => 0,
                ]);
            }

            DB::commit();
            return response()->json($meeting, 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Meeting $meeting)
    {
        Gate::authorize('view', $meeting);
        return $meeting->load('invites.user');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting)
    {
        Gate::authorize('update', $meeting);
        try {
            DB::beginTransaction();

            $meeting->update([
                'title' => $request->title ?? $meeting->title,
                'description' => $request->description ?? $meeting->description,
                'url' => $request->url ?? $meeting->url,
                'start_date' => $request->start_date ?? $meeting->start_date,
                'end_date' => $request->end_date ?? $meeting->end_date,
                'start_time' => $request->start_time ?? $meeting->start_time,
                'end_time' => $request->end_time ?? $meeting->end_time,
            ]);

            // $meeting->invites()->delete();

            // foreach ($request->participants as $value) {
            //     $meeting->invites()->create([
            //         'user_id' => $value,
            //         'status' => 0,
            //     ]);
            // }

            DB::commit();
            return response()->json($meeting, 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting)
    {
        Gate::authorize('delete', $meeting);
        try {
            DB::beginTransaction();
            $meeting->invites()->delete();
            $meeting->delete();
            DB::commit();
            return response()->json(['message' => 'Meeting deleted successfully'], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
