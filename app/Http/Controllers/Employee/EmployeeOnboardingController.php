<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\UserOnboarding;
use App\Models\Onboarding;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeOnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = Auth::id();

            $onboardings = UserOnboarding::with(["onboarding", "assignedBy"])->where("assigned_to", "=", $user)->get();

            return response()->json(["onboardings" => $onboardings]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserOnboarding $employeeOnboarding)
    {
        try {

            // an employee onboarding is connected to the parent onboarding,
            // the parent onboarding has acknowledgement and document,
            // acknowledgement and document each has user compliance
            $onboarding = $employeeOnboarding->load(
                [
                    "onboarding" =>
                        [
                            "policyAcknolwedgements" => [
                                "userAcknowledgement" => function ($query) use ($employeeOnboarding) {
                                    $query->where("acknowledged_by", "=", $employeeOnboarding->assigned_to);
                                }
                            ],
                            "requiredDocuments" => [
                                "userCompliance" => function ($query) use ($employeeOnboarding) {
                                    $query->where("complied_by", "=", $employeeOnboarding->assigned_to);
                                }
                            ]
                        ]
                ]
            );

            return response()->json(["onboarding" => $onboarding]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserOnboarding $employeeOnboarding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserOnboarding $employeeOnboarding)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserOnboarding $employeeOnboarding)
    {
        //
    }
}
