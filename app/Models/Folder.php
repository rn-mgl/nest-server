<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function parentFolder()
    {
        return $this->belongsTo(Folder::class, "id", "path");
    }

    public function childFolders()
    {
        return $this->hasMany(Folder::class, "path", "id");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }
}
