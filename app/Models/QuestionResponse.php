<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class QuestionResponse extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\QuestionResponseFactory> */
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected $with = [];

    protected $hidden = [
        'status',
        'is_correct',
        'updated_at',
        'deleted_at',
    ];


    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
