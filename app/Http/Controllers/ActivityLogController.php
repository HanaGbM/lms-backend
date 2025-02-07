<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {

        $activities = Activity::orderBy('created_at', 'desc')->paginate(10)->through(function ($query) {
            return [
                'id' => $query->id,
                'name' => $query->causer->name ?? 'System',
                'event' => $query->description,
                'created_at' => $query->created_at->toDateTimeString(),
            ];
        });

        return $activities;
    }

    public function myActivityLog()
    {
        $activities = Activity::where('causer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($query) {
                return [
                    'id' => $query->id,
                    'name' => $query->causer->name,
                    'event' => $query->description,
                    'created_at' => $query->created_at->toDateTimeString(),
                ];
            });

        return $activities;
    }
}
