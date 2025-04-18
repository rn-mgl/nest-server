<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeePerformanceReview;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $performanceReviews = DB::table("employee_performance_reviews as epr")
                                    ->join("performance_reviews as pr", function(JoinClause $join) {
                                        $join->on("pr.id", "=", "epr.performance_review_id")
                                        ->where("pr.is_deleted", "=", false);
                                    })
                                    ->join("users as u", function(JoinClause $join) {
                                        $join->on("u.id", "=", "epr.assigned_by")
                                        ->where("u.is_deleted", "=", false);
                                    })
                                    ->select([
                                        'epr.id as employee_performance_revied_id',
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
    public function show(EmployeePerformanceReviewController $EmployeePerformanceReviewController)
    {
        //
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
