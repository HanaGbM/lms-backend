<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory,
        HasUuids,
        SoftDeletes,
        InteractsWithMedia;

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

    public function getReceptRAttribute()
    {
        $lastMedia = $this->getMedia('recept')->last();

        return [
            'uuid' => $lastMedia?->uuid,
            'url' => $lastMedia ? $this->getFirstMediaUrl('recept', '', $lastMedia) : null,
            'mime_type' => $lastMedia?->mime_type,
        ];
    }
}
