<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $with = [];

    protected $hidden = [
        'status',
        'updated_at',
    ];

    protected $casts = [
        'support_subjects' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
