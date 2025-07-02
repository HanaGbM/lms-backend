<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Meeting extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
    use HasFactory,
        HasUuids,
        SoftDeletes,
        InteractsWithMedia;

    protected $guarded = [];

    protected $with = ['creator'];

    protected $casts = [
        'all_day' => 'boolean',
    ];
    protected $hidden = [
        'status',
        'meetingable_id',
        'meetingable_type',
        'updated_at',
        'deleted_at',
    ];

    protected $statusMap = [
        0 => 'created',
        1 => 'expired',
        2 => 'completed',
        3 => 'cancelled',
    ];

    public function getStatusAttribute($value)
    {
        return $this->statusMap[$value];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = array_search($value, $this->statusMap);
    }

    public function invites()
    {
        return $this->hasMany(UserInvite::class, 'meeting_id', 'id');
    }
    /**
     * Get the creator that owns the Meeting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function meetingable()
    {
        return $this->morphTo();
    }
}
