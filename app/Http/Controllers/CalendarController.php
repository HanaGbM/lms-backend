<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Test;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $dates = [];

        foreach ($dateRange as $date) {
            $dateString = $date->format('Y-m-d');
            $dates[$dateString] = [
                'date' => $dateString,
                'date_iso' => $date->toISOString(),
                'events' => [],
                'day_info' => [
                    'day_name' => $date->dayName,
                    'is_weekend' => $date->isWeekend(),
                    'month_name' => $date->monthName,
                    'year' => $date->year,
                ]
            ];
        }
        $testEvents = $this->getTestEvents($startDate, $endDate);


        foreach ($testEvents as $dateString => $events) {
            if (isset($dates[$dateString])) {
                $dates[$dateString]['events'] = $events;
            }
        }

        $meetingEvents = $this->getMeetingEvents($startDate, $endDate);

        foreach ($meetingEvents as $dateString => $events) {
            if (isset($dates[$dateString])) {
                $dates[$dateString]['events'] = array_merge($dates[$dateString]['events'], $events);
            }
        }

        // return response()->json([
        //     'requested_start' => $startDate->format('Y-m-d'),
        //     'requested_end' => $endDate->format('Y-m-d'),
        //     'total_days' => count($dates),
        //     'date_range' => $dates,
        //     'meta' => [
        //         'test_count' => array_reduce($dates, fn($carry, $date) => $carry + count($date['events']), 0)
        //     ]
        // ]);

        foreach ($dates as $date) {
            foreach ($date['events'] as $event) {
                $calendarEvents[] = [
                    'id' => (string) Str::uuid(),
                    'title' => $event['message'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'allDay' => true,
                    'extendedProps' => [
                        'calendar' => isset($event['test_id']) ? 'test' : 'meeting',
                    ],
                ];
            }
        }

        return $calendarEvents;
    }



    private function getTestEvents(Carbon $startDate, Carbon $endDate): array
    {
        $tests = Test::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('due_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('due_date', '>=', $endDate);
                });
        })
            ->get();

        $events = [];

        foreach ($tests as $test) {
            $testStart = Carbon::parse($test->start_date);
            $testEnd = Carbon::parse($test->due_date);

            $testPeriod = CarbonPeriod::create($testStart, $testEnd);

            foreach ($testPeriod as $day) {
                $dayString = $day->format('Y-m-d');

                if (!isset($events[$dayString])) {
                    $events[$dayString] = [];
                }

                $events[$dayString][] = [
                    'message' => "Test: {$test->title} from {$testStart->format('H:i')} to {$testEnd->format('H:i')}",
                    'test_id' => $test->id,
                    'start' => $test->start_date,
                    'end' => $test->due_date
                ];
            }
        }

        return $events;
    }

    private function getMeetingEvents(Carbon $startDate, Carbon $endDate): array
    {
        $meetings = Meeting::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })
            ->get();

        $events = [];

        foreach ($meetings as $meeting) {
            $meetingStart = Carbon::parse($meeting->start_date);
            $meetingEnd = Carbon::parse($meeting->end_date);

            $meetingPeriod = CarbonPeriod::create($meetingStart, $meetingEnd);

            foreach ($meetingPeriod as $day) {
                $dayString = $day->format('Y-m-d');

                if (!isset($events[$dayString])) {
                    $events[$dayString] = [];
                }

                $events[$dayString][] = [
                    'message' => "Meeting: {$meeting->title} from {$meetingStart->format('H:i')} to {$meetingEnd->format('H:i')}",
                    'meeting_id' => $meeting->id,
                    'meeting_url' => $meeting->url,
                    'start' => $meeting->start_date,
                    'end' => $meeting->end_date
                ];
            }
        }

        return $events;
    }
}
