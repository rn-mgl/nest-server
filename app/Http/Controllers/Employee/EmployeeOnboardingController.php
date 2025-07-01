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

            // get employee onboarding
            $employee_onboarding = DB::table("employee_onboardings as eo")
                                    ->select([
                                        "eo.id as employee_onboarding_id",
                                        "eo.status",
                                        "o.id as onboarding_id",
                                        "o.title",
                                        "o.description",
                                    ])
                                    ->join("onboardings as o", function(JoinClause $join) {
                                        $join->on("eo.onboarding_id", "=", "o.id")
                                        ->where("o.is_deleted", "=", false);
                                    })
                                    ->where("eo.id", $employeeOnboarding->id)
                                    ->first();

            // get policy acknowledgements
            $onboarding_policy_acknowledgements = DB::table("onboarding_policy_acknowledgements as opa")
                                                ->select([
                                                    "eopa.id as employee_onboarding_policy_acknowledgement_id",
                                                    "eopa.acknowledged",
                                                    "opa.id as onboarding_policy_acknowledgement_id",
                                                    "opa.title",
                                                    "opa.description",
                                                ])
                                                ->leftJoin("employee_onboarding_policy_acknowledgements as eopa", function(JoinClause $join) {
                                                    $join->on("opa.id", "=", "eopa.policy_acknowledgement_id")
                                                    ->where("eopa.is_deleted", "=", false);
                                                })
                                                ->where("opa.onboarding_id", "=", $employee_onboarding->onboarding_id)
                                                ->get();

            // get required documents
            $onboarding_required_documents = DB::table("onboarding_required_documents as ord")
                                                ->select([
                                                    "eord.id as employee_onboarding_required_document_id",
                                                    "eord.document",
                                                    "ord.id as onboarding_required_document_id",
                                                    "ord.title",
                                                    "ord.description",
                                                ])
                                                ->leftJoin("employee_onboarding_required_documents as eord", function (JoinClause $join) {
                                                    $join->on("ord.id", "=", "eord.required_document_id")
                                                    ->where("eord.is_deleted", "=", false);
                                                })
                                                ->where("ord.onboarding_id", "=", $employee_onboarding->onboarding_id)
                                                ->get();

            return response()->json(["onboarding" => $employee_onboarding, "policy_acknowledgements" => $onboarding_policy_acknowledgements, "required_documents" => $onboarding_required_documents]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeOnboarding $employeeOnboarding)
    {
        //
    }
}
