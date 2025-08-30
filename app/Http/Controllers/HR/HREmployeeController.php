<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Models\UserPerformanceReview;
use App\Models\UserTraining;
use Exception;
use Illuminate\Http\Request;

class HREmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "tab" => ["required", "string", "in:employees,onboardings,leaves,performances,trainings"]
            ]);

            $tab = $attributes["tab"];

            switch ($tab) {
                case "onboardings":
                    $onboardings = UserOnboarding::with(
                        [
                            "onboarding",
                            "assignedTo" => ["currentProfilePicture"],
                            "assignedBy" => ["currentProfilePicture"]
                        ]
                    )->get();

                    return response()->json(["onboardings" => $onboardings]);
                case "leaves":
                    // TO DO: Enhance to avoid N+1 Loading
                    $leaves = LeaveRequest::with([
                        "leaves",
                        "requestedBy" => ["currentProfilePicture"],
                        "approvedBy" => ["currentProfilePicture"]
                    ])->get()->map(function ($leave) {
                        $leave->balance = LeaveBalance::where(
                            [
                                "leave_type_id" => $leave->leave_type_id,
                                "user_id" => $leave->user_id
                            ]
                        )->first();

                        return $leave;
                    });
                    return response()->json(["leaves" => $leaves]);
                case "performances":
                    $performances = UserPerformanceReview::with([
                        "performanceReview",
                        "assignedUser" => ["currentProfilePicture"],
                        "assignedBy" => ["currentProfilePicture"]
                    ])->get();

                    return response()->json(["performances" => $performances]);

                case "trainings":
                    $trainings = UserTraining::with([
                        "training",
                        "assignedTo" => ["currentProfilePicture"],
                        "assignedBy" => ["currentProfilePicture"],
                    ])->get();

                    return response()->json(["trainings" => $trainings]);

                default:
                    $employees = User::with(["currentProfilePicture"])
                        ->ofRole("employee")
                        ->get();

                    return response()->json(["employees" => $employees]);
            }

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
    public function show(User $employee)
    {
        try {

            $employee->load("currentProfilePicture");

            $onboardings = UserOnboarding::with(["onboarding", "assignedBy"])
                ->where("user_onboarding.id", "=", $employee->id)
                ->get();

            // leave balances

            $leaveBalances = LeaveBalance::with(["leave", "assignedTo"])
                ->where("leave_balance.assigned_to", "=", $employee->id)
                ->get();

            $leaveRequests = LeaveRequest::with(["leave", "requestedBy"])
                ->where("leave_request.requested_by", "=", $employee->id)
                ->get();

            $performanceReviews = UserPerformanceReview::with(["performanceReview", "assignedTo"])
                ->where("user_performance_review.assigned_to", "=", $employee->id)
                ->get();

            $trainings = UserTraining::with(["training", "assignedTo"])
                ->where("user_trainings.assigned_to", "=", $employee->id)
                ->get();

            return response()
                ->json(
                    [
                        "employee" => $employee,
                        "onboardings" => $onboardings,
                        "leave_balances" => $leaveBalances,
                        "leave_requests" => $leaveRequests,
                        "performance_reviews" => $performanceReviews,
                        "trainings" => $trainings
                    ]
                );

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
