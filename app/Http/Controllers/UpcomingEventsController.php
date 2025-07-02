<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Meeting;
use App\Models\Test;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class UpcomingEventsController extends Controller
{

    public function upcomingEvents(Request $request)
    {
        $dates = [];
        $testEvents = $this->getTestEvents($request->input('module_id'));
        $events = $testEvents->map(function ($testEvent) {
            $startDate = Carbon::parse($testEvent->start_date);
            $endDate = Carbon::parse($testEvent->end_date);

            return [
                "title" => $testEvent->name,
                "start" => $startDate,
                "end" => $endDate,
            ];
        })->values();

        $meetingEvents = $this->getMeetingEvents($request->input('module_id'));

        $meetingEvents = $meetingEvents->map(function ($meetingEvent) {
            $startDate = Carbon::parse($meetingEvent->start_date);
            $endDate = Carbon::parse($meetingEvent->end_date);

            return [
                "title" => $meetingEvent->title,
                "start" => $startDate,
                "end" => $endDate,
            ];
        })->values();

        $announcementEvents = $this->getAnnouncementEvents($request->input('module_id'));

        $announcementEvents = $announcementEvents->map(function ($announcementEvent) {
            $startDate = Carbon::parse($announcementEvent->start_date);
            $endDate = Carbon::parse($announcementEvent->end_date);

            return [
                "title" => $announcementEvent->title,
                "start" => $startDate,
                "end" => $endDate,
            ];
        })->values();

        $allEvents = $events->merge($meetingEvents)->merge($announcementEvents);

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginated = $allEvents->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $paginated,
            'current_page' => (int)$page,
            'per_page' => (int)$perPage,
            'total' => $allEvents->count(),
            'last_page' => ceil($allEvents->count() / $perPage),
            'links' => [
                'first' => url()->current() . '?' . http_build_query(['page' => 1, 'per_page' => $perPage]),
                'last' => url()->current() . '?' . http_build_query(['page' => ceil($allEvents->count() / $perPage), 'per_page' => $perPage]),
                'prev' => $page > 1 ? url()->current() . '?' . http_build_query(['page' => $page - 1, 'per_page' => $perPage]) : null,
                'next' => $page < ceil($allEvents->count() / $perPage) ? url()->current() . '?' . http_build_query(['page' => $page + 1, 'per_page' => $perPage]) : null,
            ],
        ]);
    }


    private function getTestEvents($moduleId)
    {
        return Test::when(!$moduleId, function ($query) {
            $query->whereHas('studentContent', function ($q) {
                $q->where('student_id', Auth::id());
            });
        })->when($moduleId, function ($query) use ($moduleId) {
            $query->whereHas('testable', function ($q) use ($moduleId) {
                $q->where('testable_id', $moduleId);
            });
        })->orWhere('created_by', Auth::id())
            ->where('is_active', true)
            ->get();
    }

    private function getMeetingEvents($moduleId)
    {
        return Meeting::when(!$moduleId, function ($query) {
            $query->whereHas('invites', function ($q) {
                $q->where('user_id', Auth::id());
            });
        })->when($moduleId, function ($query) use ($moduleId) {
            $query->where('meetingable_id', $moduleId);
        })->orWhere('created_by', Auth::id())->get();
    }

    private function getAnnouncementEvents($moduleId)
    {
        return Announcement::when(!$moduleId, function ($query) {
            $query->whereHas('studentContent', function ($q) {
                $q->where('student_id', Auth::id());
            });
        })->when($moduleId, function ($query) use ($moduleId) {
            $query->where('announcementable_id', $moduleId);
        })->orWhere('created_by', Auth::id())
            ->where('is_active', true)
            ->get();
    }
}
