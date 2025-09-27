<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOnboardingRequiredDocuments extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function requirement()
    {
        return $this->belongsTo(OnboardingRequiredDocument::class, "required_document_id", "id");
    }

    public function compliedBy()
    {
        return $this->belongsTo(User::class, "complied_by", "id");
    }

    /**
     * Summary of document
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<File, UserOnboardingRequiredDocuments>
     */
    public function document()
    {
        return $this->morphOne(File::class, "fileable")->latest();
    }


}
