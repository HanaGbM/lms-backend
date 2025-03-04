<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class ChapterMaterial extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ChapterMaterialFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];
    protected $appends = ['files'];

    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'media',
        'status',
        'updated_at',
        'deleted_at',
    ];

    public function getFilesAttribute()
    {
        return  $this->getMedia('file')->map(function ($file) {
            return [
                'id' => $file->uuid,
                'name' => $file->file_name,
                'url' => $file->getUrl(),
                'size' => $file->size,
                'mime_type' => $file->mime_type,
            ];
        });
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chapterMaterial) {
            if (empty($chapterMaterial->created_by) && Auth::check()) {
                $chapterMaterial->created_by = Auth::id();
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
