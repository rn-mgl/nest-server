<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function document()
    {
        return $this->morphOne(File::class, "fileable");
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, "path", "id");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }
}
