<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function employeeOnboarding()
    {
        return $this->hasMany(EmployeeOnboarding::class, "employee_onboarding_id", "onboarding_id");
    }

    public function policyAcknowledgements()
    {
        return $this->hasMany(OnboardingPolicyAcknowledgements::class)->where("is_deleted", false);
    }

    public function requiredDocuments()
    {
        return $this->hasMany(OnboardingRequiredDocuments::class)->where("is_deleted", false);
    }
}
