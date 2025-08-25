<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $guarded = [];

    /**
     * Summary of fileable
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, File>
     */
    public function fileable()
    {
        return $this->morphTo();
    }

}
