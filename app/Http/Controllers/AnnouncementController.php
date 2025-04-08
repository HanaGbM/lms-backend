<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        Gate::authorize('viewAny', Announcement::class);

        $announcements = Announcement::orderBy('created_at', 'desc')
            ->where('created_by', auth()->id())
            ->paginate(10)->through(function ($query) {
                return [
                    'id' => $query->id,
                    'title' => $query->title,
                    'content' => $query->content,
                    'created_at' => $query->created_at->toDateTimeString(),
                ];
            });

        return response()->json($announcements);
    }

    public function getAnnouncements()
    {

        Gate::authorize('viewAny', Announcement::class);

        $announcements = Announcement::orderBy('created_at', 'desc')
            ->where(function ($query) {
                $query->where('is_custom', true)
                    ->whereHas('studentContent', function ($query) {
                        $query->where('student_id', auth()->id());
                    })
                    ->orWhere('is_custom', false);
            })
            ->paginate(10)
            ->through(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'is_custom' => $announcement->is_custom,
                    'created_at' => $announcement->created_at->toDateTimeString(),
                ];
            });

        return response()->json($announcements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request)
    {
        try {
            Gate::authorize('create', Announcement::class);

            DB::beginTransaction();

            $announcement = Announcement::create([
                'title' => $request->title,
                'content' => $request->content,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_custom' => $request->is_custom,
            ]);

            if ($request->is_custom) {
                foreach ($request->student_ids as  $value) {
                    $announcement->studentContent()->create([
                        'student_id' => $value,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Announcement created successfully',
                'announcement' => $announcement,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        //
    }
}
