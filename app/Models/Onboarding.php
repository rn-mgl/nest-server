<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onboarding extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Summary of createdBy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Onboarding>
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }

    /**
     * Summary of policyAcknowledgements
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingPolicyAcknowledgement, Onboarding>
     */
    public function policyAcknowledgements()
    {
        return $this->hasMany(OnboardingPolicyAcknowledgement::class, "onboarding_id", "id");
    }

    /**
     * Summary of requiredDocuments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingRequiredDocument, Onboarding>
     */
    public function requiredDocuments()
    {
        return $this->hasMany(OnboardingRequiredDocument::class, "onboarding_id", "id");
    }
}
