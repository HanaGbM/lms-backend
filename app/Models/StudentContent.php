<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentContent extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = [];
    protected $with = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'status',
        'updated_at',
        'deleted_at',
        'contentable_type',
        'contentable_id',
    ];

    public function contentable()
    {
        return $this->morphTo();
    }
}
