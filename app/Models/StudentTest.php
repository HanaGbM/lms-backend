<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $with = [];

    protected $hidden = [
        'status',
        'updated_at',
        'deleted_at',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * Get the test that owns the StudentTest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id', 'id');
    }
}
