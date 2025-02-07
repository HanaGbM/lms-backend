<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Module extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['cover'];
    protected $with = [];

    protected $hidden = [
        'media',
        'status',
        'updated_at',
        'deleted_at',
    ];


    protected $statusMap = [
        0 => 'Inactive',
        1 => 'Active',
    ];

    public function getStatusAttribute($value)
    {
        return $this->statusMap[$value];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = array_search($value, $this->statusMap);
    }

    public static function getStatusMap()
    {
        return (new static)->statusMap;
    }

    public function getCoverAttribute()
    {
        $lastMedia = $this->getMedia('cover')->last();

        return [
            'uuid' => $lastMedia?->uuid,
            'url' => $lastMedia ? $this->getFirstMediaUrl('cover', '', $lastMedia) : null,
            'mime_type' => $lastMedia?->mime_type,
        ];
    }

    /**
     * Get the createdBY that owns the Module
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreign_key', 'other_key');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $modelName = class_basename($this);

                if ($eventName === 'created') {
                    return "{$modelName} with title '{$this->title}' has been created.";
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
