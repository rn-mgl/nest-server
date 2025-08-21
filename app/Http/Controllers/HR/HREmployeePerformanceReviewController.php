<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\UserPerformanceReview;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HREmployeePerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "performance_review_id" => ["required", "integer", "exists:performance_reviews,id"]
            ]);

            $employees = DB::table("users as u")
                        ->leftJoin("employee_performance_reviews as epr", function(JoinClause $join) use($attributes) {
                            $join->on("u.id", "=", "epr.user_id")
                            ->where("epr.performance_review_id", "=", $attributes["performance_review_id"]);
                        })
                        ->select([
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "u.email_verified_at",
                            "u.created_at",
                            "epr.id as employee_performance_review_id"
                        ])
                        ->get();

            return response()->json(["employees" => $employees]);

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
        try {
            $attributes = $request->validate([
                "user_ids" => ["required", "array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "performance_review_id" => ["required", "integer", "exists:performance_reviews,id"]
            ]);

            $performanceReviewId = $attributes["performance_review_id"];
            $employeeIds = $attributes["user_ids"];

            $performanceReviews = UserPerformanceReview::where("performance_review_id", "=", $performanceReviewId)->get();

            $alreadyAssigned = $performanceReviews->pluck("user_id")->toArray();

            foreach ($employeeIds as $employee) {
                if (!in_array($employee, $alreadyAssigned)) {

                    $employeePerformanceReviewAttr = [
                        "performance_review_id" => $performanceReviewId,
                        "user_id" => $employee,
                        "assigned_by" => Auth::id()
                    ];

                    $created = UserPerformanceReview::create($employeePerformanceReviewAttr);
                }
            }

            foreach ($alreadyAssigned as $id) {
                if (!in_array($id, $employeeIds)) {
                    $deleted = UserPerformanceReview::where("user_id", "=", $id)
                                ->where("performance_review_id", "=", $performanceReviewId)
                                ->delete();
                }
            }

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
