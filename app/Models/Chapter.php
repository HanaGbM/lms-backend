<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Chapter extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
        'is_custom' => 'boolean',
    ];

    protected $hidden = [
        'media',
        'status',
        'updated_at',
        'deleted_at',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get all of the materials for the Chapter
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function materials(): HasMany
    {
        return $this->hasMany(ChapterMaterial::class, 'chapter_id', 'id');
    }

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
