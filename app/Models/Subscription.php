<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory,
        HasUuids,
        SoftDeletes;

    protected $guarded = [];

    protected $with = [];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected $statusMap = [
        0 => 'Expired',
        1 => 'Active',
        3 => 'Cancelled',
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

    public function studentModule(): BelongsTo
    {
        return $this->belongsTo(StudentModule::class, 'student_module_id', 'id');
    }

    public function student()
    {
        return $this->studentModule->student;
    }

    public function module()
    {
        return $this->studentModule->module;
    }
}
