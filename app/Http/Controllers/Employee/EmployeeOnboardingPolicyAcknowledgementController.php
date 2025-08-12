<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeOnboardingPolicyAcknowledgement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            $user = Auth::guard("base")->id();

            $acknowledgement_attributes = [
                "employee_id" => $user,
                "policy_acknowledgement_id" => $attributes["policy_acknowledgement_id"],
                "acknowledged" => $attributes["policy_acknowledged"]
            ];

            $acknowledged = EmployeeOnboardingPolicyAcknowledgement::create($acknowledgement_attributes);

            return response()->json(["success" => $acknowledged]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeOnboardingPolicyAcknowledgement $employeeOnboardingPolicyAcknowledgement)
    {
        //
    }
}
