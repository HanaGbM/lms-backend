<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserInvite extends Model
{
    /** @use HasFactory<\Database\Factories\UserInviteFactory> */
    use HasFactory,
        HasUuids,
        SoftDeletes,
        InteractsWithMedia;

    protected $guarded = [];

    protected $with = [];

    protected $hidden = [
        'meeting_id',
        'user_id',
        'updated_at',
        'deleted_at',
    ];

    protected $statusMap = [
        0 => 'pending',
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


    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
