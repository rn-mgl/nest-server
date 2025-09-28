<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\UserOnboarding;
use App\Models\UserPerformanceReview;
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
    public function index()
    {
        try {

            $user = Auth::id();

            $performanceReviews = UserPerformanceReview::with(["performanceReview", "assignedBy"])
                ->where("assigned_to", "=", $user)
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
    public function show(UserPerformanceReview $employeePerformanceReview)
    {
        try {

            $employeePerformanceReview->load(
                [
                    "performanceReview" => [
                        "surveys" => [
                            "userResponse" => function ($query) use ($employeePerformanceReview) {
                                $query->where("response_from", "=", $employeePerformanceReview->assigned_to);
                            }
                        ]
                    ]
                ]
            );

            return response()->json(["performance_review" => $employeePerformanceReview]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPerformanceReview $employeePerformanceReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserPerformanceReview $employeePerformanceReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPerformanceReview $employeePerformanceReview)
    {
        //
    }
}
