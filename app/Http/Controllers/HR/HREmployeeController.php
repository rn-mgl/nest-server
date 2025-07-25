<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HREmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SearchRequest $searchRequest, SortRequest $sortRequest, CategoryRequest $categoryRequest)
    {
        try {

            $validated = $request->validate([
                "tab" => ["required", "string", "in:employees,onboardings,leaves,performances,trainings"]
            ]);

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $categoryAttributes = $categoryRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes, $categoryAttributes, $validated);

            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $sortKey = $attributes["sortKey"];

            $searchKey = $attributes["searchKey"];
            $searchValue = $attributes["searchValue"] ?? "";

            $categoryKey = $attributes["categoryKey"];
            $categoryValue = $attributes["categoryValue"];

            $tab = $attributes["tab"];

            if ($tab === "employees") {
                $verified = $categoryValue === "All" ? "" : $categoryValue === "Verified";

                $employees = DB::table("users as u")
                                ->select([
                                    "u.id as user_id",
                                    "u.first_name",
                                    "u.last_name",
                                    "u.email",
                                    "u.image",
                                    "u.email_verified_at",
                                    "u.created_at",
                                ])
                                ->where("role", "=", "employee")
                                ->when($verified === true, fn($query) => $query->whereNotNull("email_verified_at"))
                                ->when($verified === false, fn($query) => $query->whereNull("email_verified_at"))
                                ->whereLike($searchKey, "%$searchValue%")
                                ->orderBy($sortKey, $sortType)
                                ->get();

                return response()->json(["employees" => $employees]);
            }

            if ($tab === "onboardings") {
                $categoryValue = $categoryValue === "All" ? "" : $categoryValue;

                $onboardings = DB::table("employee_onboardings as eo")
                                    ->select([
                                        "eo.id as employee_onboarding_id",
                                        "eo.assigned_by",
                                        "eo.status",
                                        "eo.created_at",
                                        "o.id as onboarding_id",
                                        "o.title",
                                        "o.description",
                                        "u.id as user_id",
                                        "u.first_name as first_name",
                                        "u.last_name as last_name",
                                        "u.email as email",
                                        "u.image"
                                    ])
                                    ->join("onboardings as o", function(JoinClause $join) {
                                        $join->on("o.id", "=", "eo.onboarding_id")
                                        ->where("o.is_deleted", "=", false);
                                    })
                                    ->join("users as u", function(JoinClause $join) {
                                        $join->on("u.id", "=", "eo.employee_id")
                                        ->where("u.is_deleted", "=", false);
                                    })
                                    ->where("eo.is_deleted", "=", false)
                                    ->whereLike($searchKey,"%{$searchValue}%")
                                    ->whereLike($categoryKey, "%{$categoryValue}%")
                                    ->orderBy($sortKey, $sortType)
                                    ->get();

                return response()->json(["onboardings" => $onboardings]);
            }

            if ($tab === "leaves") {
                $leaves = DB::table("leave_requests as lr")
                            ->select([
                                "lr.id as leave_request_id",
                                "lr.approved_by",
                                "lr.start_date",
                                "lr.end_date",
                                "lr.status",
                                "lr.reason",
                                "lr.created_at",
                                "lb.id as leave_balance_id",
                                "lb.balance",
                                "lt.id as leave_type_id",
                                "lt.type",
                                "u.id as user_id",
                                "u.first_name",
                                "u.last_name",
                                "u.email",
                                "u.image"
                            ])
                            ->join("leave_types as lt", function(JoinClause $join) {
                                $join->on("lt.id", "=", "lr.leave_type_id")
                                ->where("lt.is_deleted", "=", false);
                            })
                            ->join("leave_balances as lb", function(JoinClause $join) {
                                $join->on("lb.leave_type_id", "=", "lt.id")
                                ->where("lb.is_deleted", "=", false)
                                ->whereColumn("lb.user_id", "=", "lr.user_id");
                            })
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("u.id", "=", "lr.user_id")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("lr.is_deleted", "=", false)
                            ->get();

                return response()->json(["leaves" => $leaves]);
            }

            return response()->json(["data" => []]);

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
    public function show(string $employee_id)
    {
        try {

            $employee = DB::table("users")
                        ->select([
                            "id as user_id",
                            "first_name",
                            "last_name",
                            "email",
                            "email_verified_at",
                            "image",
                        ])
                        ->where("id", "=", $employee_id)
                        ->where("role", "=", "employee")
                        ->where("is_deleted", false)
                        ->firstOrFail();

            // onboarding
            $onboardings = DB::table("employee_onboardings as eo")
                            ->select([
                                "o.id as onboarding_id",
                                "o.title",
                                "o.description",
                                "o.created_by",
                                "eo.id as employee_onboarding_id",
                                "eo.status",
                                "u.id as user_id",
                                "u.first_name",
                                "u.last_name",
                                "u.email",
                            ])
                            ->join("onboardings as o", function(JoinClause $join) {
                                $join->on("o.id", "=", "eo.onboarding_id")
                                ->where("o.is_deleted", "=", false);
                            })
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("u.id", "=", "o.created_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("eo.is_deleted", "=", false)
                            ->where("eo.employee_id", "=", $employee_id)
                            ->get();

            // leave balances
            $leave_balances = DB::table("leave_balances as lb")
                                ->select([
                                    "lb.id as leave_balance_id",
                                    "lb.balance",
                                    "lt.id as leave_type_id",
                                    "lt.type",
                                    "lt.description",
                                    "lt.created_by",
                                    "u.first_name",
                                    "u.last_name",
                                    "u.email",
                                    "u.id as user_id"
                                ])
                                ->join("leave_types as lt", function(JoinClause $join) {
                                    $join->on("lt.id", "=", "lb.leave_type_id")
                                    ->where("lt.is_deleted", "=", false);
                                })
                                ->join("users as u", function(JoinClause $join) {
                                    $join->on("u.id", "=", "lt.created_by")
                                    ->where('u.is_deleted', "=", false);
                                })
                                ->where("lb.user_id", "=", $employee_id)
                                ->where("lb.is_deleted", "=", false)
                                ->get();

            // leave requests
            $leave_requests = DB::table("leave_requests as lr")
                                ->select([
                                    "lt.id as leave_type_id",
                                    "lt.type",
                                    "lt.description",
                                    "lr.id as leave_request_id",
                                    "lr.start_date",
                                    "lr.end_date",
                                    "lr.reason",
                                    "lr.approved_by",
                                    "lr.status",
                                    "u.first_name",
                                    "u.last_name",
                                    "u.email",
                                    "u.id as user_id"
                                ])
                                ->join("leave_types as lt", function(JoinClause $join) {
                                    $join->on("lt.id", "=", "lr.leave_type_id")
                                    ->where("lt.is_deleted", "=", false);
                                })
                                ->join("users as u", function(JoinClause $join) {
                                    $join->on("u.id", "=", "lt.created_by")
                                    ->where("u.is_deleted", "=", false);
                                })
                                ->where("lr.user_id", "=", $employee_id)
                                ->where("lr.is_deleted", "=", false)
                                ->get();

            // performance
            $performance_reviews = DB::table("employee_performance_reviews as epr")
                                    ->select([
                                        "pr.id as performance_review_id",
                                        "pr.title",
                                        "pr.description",
                                        "epr.id as employee_performance_review_id",
                                        "epr.status",
                                        "u.id as user_id",
                                        "u.first_name",
                                        "u.last_name",
                                        "u.email"
                                    ])
                                    ->join("performance_reviews as pr", function(JoinClause $join) {
                                        $join->on("pr.id", "=", "epr.performance_review_id")
                                        ->where("pr.is_deleted", "=", false);
                                    })
                                    ->join("users as u", function(JoinClause $join) {
                                        $join->on("u.id", "=", "pr.created_by")
                                        ->where("u.is_deleted", "=", false);
                                    })
                                    ->where("epr.employee_id", "=", $employee_id)
                                    ->where("epr.is_deleted", "=", false)
                                    ->get();
            // training
            $trainings = DB::table("employee_trainings as et")
                        ->select([
                            "t.id as training_id",
                            "t.title",
                            "t.description",
                            "t.deadline_days",
                            "t.created_by",
                            DB::raw("CASE WHEN et.score IS NOT NULL THEN t.certificate ELSE NULL END AS certificate"),
                            "et.status",
                            "et.score",
                            "et.deadline",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "u.id as user_id"
                        ])
                        ->join("trainings as t", function (JoinClause $join) {
                            $join->on("t.id", "=", "et.training_id")
                            ->where("t.is_deleted", "=", false);
                        })
                        ->join("users as u", function(JoinClause $join) {
                            $join->on("u.id", "=", "t.created_by")
                            ->where("u.is_deleted", "=", false);
                        })
                        ->where("et.employee_id", "=", $employee_id)
                        ->where("et.is_deleted", "=", false)
                        ->get();


            return response()
                    ->json(
                        [
                            "employee" => $employee,
                            "onboardings" => $onboardings,
                            "leave_balances" => $leave_balances,
                            "leave_requests" => $leave_requests,
                            "performance_reviews" => $performance_reviews,
                            "trainings" => $trainings
                        ]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
