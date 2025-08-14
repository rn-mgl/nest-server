<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes);

            $searchKey = $attributes["searchKey"];
            $sortKey = $attributes["sortKey"];
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? "";

            $user = Auth::id();

            $performanceReviews = DB::table("employee_performance_reviews as epr")
                                    ->join("performance_reviews as pr", function(JoinClause $join) {
                                        $join->on("pr.id", "=", "epr.performance_review_id")
                                        ->where("pr.is_deleted", "=", false);
                                    })
                                    ->join("users as u", function(JoinClause $join) {
                                        $join->on("u.id", "=", "epr.assigned_by")
                                        ->where("u.is_deleted", "=", false);
                                    })
                                    ->where("epr.employee_id", "=", $user)
                                    ->where("{$searchKey}", "LIKE", "%{$searchValue}%")
                                    ->orderBy("{$sortKey}", "{$sortType}")
                                    ->select([
                                        'epr.id as employee_performance_review_id',
                                        'pr.id as performance_review_id',
                                        'pr.title',
                                        'pr.description',
                                        'pr.created_by',
                                        'u.id as user_id',
                                        'u.first_name',
                                        'u.last_name',
                                        'u.email',
                                        'u.email_verified_at',
                                        'u.created_at',
                                    ])
                                    ->get();

            return response()->json(["performance_reviews" => $performanceReviews]);

        } catch (\Throwable $th) {
            throw $th;
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
    public function show($employeePerformanceReview)
    {
        try {

            $user = Auth::id();

            $performanceReview = DB::table("employee_performance_reviews as epr")
                                ->join("performance_reviews as pr", function(JoinClause $join) {
                                    $join->on("epr.performance_review_id", "=", "pr.id")
                                    ->where("pr.is_deleted", "=", false);
                                })
                                ->select([
                                    "pr.id as performance_review_id",
                                    "pr.title",
                                    "pr.description",
                                    "pr.created_by"
                                ])
                                ->where("epr.id", "=", $employeePerformanceReview)
                                ->first();

            $performanceReview->contents = DB::table("performance_review_contents as prc")
                                            ->leftJoin("employee_performance_review_responses as eprr", function(JoinClause $join) use ($user) {
                                                $join->on("prc.id", "=", "eprr.performance_review_content_id")
                                                ->where("eprr.response_by", "=", $user)
                                                ->where("prc.is_deleted", "=", false);
                                            })
                                            ->where("prc.performance_review_id", "=", $performanceReview->performance_review_id)
                                            ->select([
                                                "prc.id as performance_review_content_id",
                                                "prc.survey",
                                                "eprr.id as employee_performance_review_response_id",
                                                "eprr.response",
                                            ])
                                            ->get();

            return response()->json(["performance_review" => $performanceReview]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeePerformanceReviewController $EmployeePerformanceReviewController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeePerformanceReviewController $EmployeePerformanceReviewController)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeePerformanceReviewController $EmployeePerformanceReviewController)
    {
        //
    }
}
