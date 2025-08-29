<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $guarded = [];

    /**
     * Automatically append values on return of instance
     * @var array
     */
    protected $appends = ['url'];

    protected $hidden = [
        'disk',
        'path',
        'fileable_type',
        'fileable_id'
    ];

    /**
     * Summary of fileable
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, File>
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * Always append a the url attribute to the model's array and JSON representations.
     *
     * This ensures that the attribute is included whenever the model is serialized.
     *
     * @return Attribute
     */
    public function url() : Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk($this->disk)->url($this->path) ?? null
        );
    }

}
