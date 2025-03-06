<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StudentModule extends Model
{
    /** @use HasFactory<\Database\Factories\StudentModuleFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $with = ['student'];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected $statusMap = [
        0 => 'Pending',
        1 => 'Payment Waiting',
        2 => 'Payment Failed',
        3 => 'Approved',
        4 => 'In Progress',
        5 => 'Completed',
        6 => 'Cancelled',
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

    /**
     * Get the module that owns the StudentModule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moduleTeacher(): BelongsTo
    {
        return $this->belongsTo(ModuleTeacher::class, 'module_teacher_id', 'id');
    }

    /**
     * Get the student that owns the StudentModule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($studentModule) {
            if (empty($studentModule->created_by) && Auth::check()) {
                $studentModule->created_by = Auth::id();
            }
        });
    }
}
