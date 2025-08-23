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
            $categoryKey = $attributes['categoryKey'];
            $categoryValue = $attributes['categoryValue'] ?? "";
            $categoryValue = $categoryValue === "all" ? "" : $categoryValue;

            $user = Auth::id();

            $onboardings = DB::table("user_onboardings as uo")
                            ->join("onboardings as o", function(JoinClause $join) {
                                $join->on("uo.onboarding_id", "=", "o.id")
                                ->where("o.deleted_at", "=", false);
                            })
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("o.created_by", "=", "u.id")
                                ->where("u.deleted_at", "=", false);
                            })
                            ->where("user_id", "=", $user)
                            ->where("{$searchKey}", "LIKE", "%{$searchValue}%")
                            ->where("{$categoryKey}", "LIKE", "%{$categoryValue}%")
                            ->orderBy("{$sortKey}", $sortType)
                            ->select([
                                'uo.id as user_onboarding_id',
                                'uo.status',
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
    public function show(UserOnboarding $employeeOnboarding)
    {
        try {

            // get employee onboarding
            $onboarding = DB::table("user_onboardings as uo")
                                    ->select([
                                        "uo.id as user_onboarding_id",
                                        "uo.status",
                                        "o.id as onboarding_id",
                                        "o.title",
                                        "o.description",
                                    ])
                                    ->join("onboardings as o", function(JoinClause $join) {
                                        $join->on("uo.onboarding_id", "=", "o.id")
                                        ->where("o.deleted_at", "=", false);
                                    })
                                    ->where("uo.id", $employeeOnboarding->id)
                                    ->first();

            // get policy acknowledgements
            $onboarding_policy_acknowledgements = DB::table("onboarding_policy_acknowledgements as opa")
                                                ->select([
                                                    "uopa.id as user_onboarding_policy_acknowledgement_id",
                                                    "uopa.acknowledged",
                                                    "opa.id as onboarding_policy_acknowledgement_id",
                                                    "opa.title",
                                                    "opa.description",
                                                ])
                                                ->leftJoin("user_onboarding_policy_acknowledgements as uopa", function(JoinClause $join) {
                                                    $join->on("opa.id", "=", "uopa.policy_acknowledgement_id")
                                                    ->where("uopa.deleted_at", "=", false);
                                                })
                                                ->where("opa.onboarding_id", "=", $onboarding->onboarding_id)
                                                ->orderBy("opa.id")
                                                ->get();

            // get required documents
            $onboarding_required_documents = DB::table("onboarding_required_documents as ord")
                                                ->select([
                                                    "uord.id as user_onboarding_required_document_id",
                                                    "uord.document",
                                                    "ord.id as onboarding_required_document_id",
                                                    "ord.title",
                                                    "ord.description",
                                                ])
                                                ->leftJoin("user_onboarding_required_documents as uord", function (JoinClause $join) {
                                                    $join->on("ord.id", "=", "uord.required_document_id")
                                                    ->where("uord.deleted_at", "=", false);
                                                })
                                                ->where("ord.onboarding_id", "=", $onboarding->onboarding_id)
                                                ->orderBy("ord.id")
                                                ->get();

            return response()->json(["onboarding" => $onboarding, "policy_acknowledgements" => $onboarding_policy_acknowledgements, "required_documents" => $onboarding_required_documents]);
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
