<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\UserOnboardingPolicyAcknowledgement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeOnboardingPolicyAcknowledgementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $attributes = $request->validate([
                "policy_acknowledged" => ["required", "boolean"],
                "policy_acknowledgement_id" => ["required", "integer"]
            ]);

            $user = Auth::id();

            $acknowledgementAttributes = [
                "user_id" => $user,
                "policy_acknowledgement_id" => $attributes["policy_acknowledgement_id"],
                "acknowledged" => $attributes["policy_acknowledged"]
            ];

            $acknowledged = UserOnboardingPolicyAcknowledgement::create($acknowledgementAttributes);

            return response()->json(["success" => $acknowledged]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }
}
