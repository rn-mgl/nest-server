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
     * Get all of the files for the UserOnboardingRequiredDocuments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<File, UserOnboardingRequiredDocuments>
     */
    public function userRequiredDocuments()
    {
        return $this->morphMany(File::class, "fileable");
    }


}
