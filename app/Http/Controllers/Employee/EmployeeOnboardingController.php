<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\EmployeeOnboarding;
use App\Models\Onboarding;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeOnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest, CategoryRequest $categoryRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $categoryAttributes = $categoryRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes, $categoryAttributes);

            $searchKey = $attributes['searchKey'];
            $searchValue = $attributes['searchValue'] ?? "";
            $sortKey = $attributes['sortKey'];
            $isAsc = filter_var($attributes['isAsc'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $categoryKey = $attributes['categoryKey'] ?? "";
            $categoryValue = $attributes['categoryValue'] ?? "";
            $categoryValue = $categoryValue === "all" ? "" : $categoryValue;

            $user = Auth::guard("base")->id();

            $onboardings = DB::table("employee_onboardings as eo")
                            ->join("onboardings as o", function(JoinClause $join) {
                                $join->on("eo.onboarding_id", "=", "o.id")
                                ->where("o.is_deleted", "=", false);
                            })
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("o.created_by", "=", "u.id")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("employee_id", "=", $user)
                            ->where("{$searchKey}", "LIKE", "%{$searchValue}%")
                            ->where("{$categoryKey}", "LIKE", "%{$categoryValue}%")
                            ->orderBy("{$sortKey}", $sortType)
                            ->select([
                                'eo.id as employee_onboarding_id',
                                'eo.completed_documents',
                                'eo.policy_acknowledged',
                                'o.id as onboarding_id',
                                'o.title',
                                'o.description',
                                'o.created_by',
                                'u.id as user_id',
                                'u.first_name',
                                'u.last_name',
                                'u.email',
                                'u.email_verified_at',
                                'u.created_at',
                            ])
                            ->get();

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
    public function show(EmployeeOnboarding $employeeOnboarding)
    {
        try {
            return response()->json(["onboarding" => $employeeOnboarding->load([
                "onboarding",
                "onboarding.policyAcknowledgements",
                "onboarding.requiredDocuments",
                "assignedBy"
                ])]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeOnboarding $employeeOnboarding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeOnboarding $employeeOnboarding)
    {
        try {
            $attributes = $request->validate([
                "policy_acknowledged" => ["boolean", "required"]
            ]);

            $acknowledged = $employeeOnboarding->update($attributes);

            return response()->json(["success" => $acknowledged]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeOnboarding $employeeOnboarding)
    {
        //
    }
}
