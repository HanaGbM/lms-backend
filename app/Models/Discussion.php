<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Discussion extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\DiscussionFactory> */
    use HasFactory,
        HasUuids,
        SoftDeletes,
        InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['image'];
    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    protected $hidden = [
        'media',
        'status',
        'discussable_id',
        'discussable_type',
        'updated_at',
        'deleted_at',
    ];


    public function discussable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getImageAttribute()
    {
        $lastMedia = $this->getMedia('image')->last();

        return [
            'uuid' => $lastMedia?->uuid,
            'url' => $lastMedia ? $this->getFirstMediaUrl('image', '', $lastMedia) : null,
            'mime_type' => $lastMedia?->mime_type,
        ];
    }
}
