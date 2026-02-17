<?php

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

class Question extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['file'];
    protected $with = ['options'];
    protected $casts = [
        'is_active' => 'boolean',
        'score_value' => 'float',
    ];
    protected $hidden = [
        'media',
        'status',
        'updated_at',
        'deleted_at',
    ];

    public function getFileAttribute()
    {
        $lastMedia = $this->getMedia('file')->last();

        return [
            'uuid' => $lastMedia?->uuid,
            'url' => $lastMedia ? $this->getFirstMediaUrl('file', '', $lastMedia) : null,
            'mime_type' => $lastMedia?->mime_type,
        ];
    }

    /**
     * Get the test that owns the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id', 'id');
    }


    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'id');
    }

    /**
     * Get all of the responses for the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class, 'question_id', 'id');
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
