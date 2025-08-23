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
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Summary of assignedUsers
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User, Onboarding, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, "onboarding_user", "onboarding_id", "user_id");
    }

    /**
     * Summary of policyAcknolwedgements
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingPolicyAcknowledgement, Onboarding>
     */
    public function policyAcknolwedgements()
    {
        return $this->hasMany(OnboardingPolicyAcknowledgement::class, "id", "onboarding_id");
    }

    /**
     * Summary of requiredDocuments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OnboardingRequiredDocument, Onboarding>
     */
    public function requiredDocuments()
    {
        return $this->hasMany(OnboardingRequiredDocument::class, "id", "onboarding_id");
    }
}
