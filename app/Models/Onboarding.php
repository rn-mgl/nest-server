<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Summary of createdBy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Onboarding>
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, "onboarding_user", "onboarding_id", "user_id");
    }

    /**
     * Summary of policyAcknolwedgements
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingPolicyAcknowledgements, Onboarding>
     */
    public function policyAcknolwedgements()
    {
        return $this->hasMany(OnboardingPolicyAcknowledgements::class, "id", "onboarding_id");
    }

    /**
     * Summary of requiredDocuments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingRequiredDocuments, Onboarding>
     */
    public function requiredDocuments()
    {
        return $this->hasMany(OnboardingRequiredDocuments::class, "id", "onboarding_id");
    }
}
