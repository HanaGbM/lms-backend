<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleTeacher extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];
    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    protected $hidden = [
        'status',
        'module_id',
        'module_id',
        'teacher_id',
        'updated_at',
        'deleted_at',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function tests(): MorphMany
    {
        return $this->morphMany(Test::class, 'testable');
    }

    /**
     * Get all of the students for the ModuleTeacher
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(StudentModule::class, 'module_teacher_id');
    }

    public function discussions()
    {
        return $this->morphMany(Discussion::class, 'discussable');
    }
}
