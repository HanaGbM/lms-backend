<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Announcement extends Model
{
    /** @use HasFactory<\Database\Factories\AnnouncementFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $guarded = [];

    protected $appends = [];
    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_custom' => 'boolean',
    ];
    protected $hidden = [
        'status',
        'created_by',
        'updated_at',
        'deleted_at',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chapter) {
            if (empty($chapter->created_by) && Auth::check()) {
                $chapter->created_by = Auth::id();
            }
        });
    }

    public function studentContent()
    {
        return $this->morphMany(StudentContent::class, 'contentable');
    }

    public function announcementable()
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $modelName = class_basename($this);

                if ($eventName === 'created') {
                    return "{$modelName} with name '{$this->name}' has been created.";
                } elseif ($eventName === 'updated') {
                    $changes = collect($this->getChanges())
                        ->except(['updated_at'])
                        ->map(function ($newValue, $key) {
                            $oldValue = $this->getOriginal($key);

                            return "{$key}: '{$oldValue}' to '{$newValue}'";
                        })->implode(', ');

                    return "{$modelName} has been updated. Changes: {$changes}";
                } elseif ($eventName === 'deleted') {
                    return "{$modelName} with name '{$this->name}' has been deleted.";
                }

                return "{$modelName} has been {$eventName}.";
            });
    }
}
