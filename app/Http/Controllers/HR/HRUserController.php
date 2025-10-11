<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Models\UserPerformanceReview;
use App\Models\UserTraining;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class HRUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "tab" => ["required", "string", "in:users,onboardings,leaves,performances,trainings,attendances"],
                "date" => ["string", "date"]
            ]);

            $tab = $attributes["tab"];

            switch ($tab) {
                case "attendances":
                    $parsedDate = Carbon::parse($attributes["date"]) ?? Carbon::now();
                    $lateThreshold = $parsedDate->copy()->addHours(6);

                    $attendances = User::with(
                        [
                            "attendances" => fn($query) => $query->whereDate("login_time", "=", $parsedDate),
                            "image"
                        ]
                    )->get()->map(function ($user) use ($lateThreshold) {
                        $attendance = $user->attendances->first();
                        $user->unsetRelation('attendances');

                        if (!$attendance) {
                            $user->attendance = [
                                'id' => null,
                                'user_id' => $user->id,
                                'login_time' => null,
                                'logout_time' => null,
                                'late' => null,
                                'absent' => true
                            ];

                            return $user;
                        }

                        $attendance->late = Carbon::parse($attendance->login_time)->greaterThan($lateThreshold);
                        $attendance->absent = false;

                        $user->attendance = $attendance;

                        return $user;
                    });

                    return response()->json(["attendances" => $attendances]);

                case "onboardings":
                    $onboardings = UserOnboarding::with(
                        [
                            "onboarding",
                            "assignedTo" => ["image"],
                            "assignedBy" => ["image"]
                        ]
                    )->get();

                    return response()->json(["onboardings" => $onboardings]);
                case "leaves":
                    // TO DO: Enhance to avoid N+1 Loading
                    $leaves = LeaveRequest::with([
                        "leave",
                        "requestedBy" => ["image"],
                        "actionedBy" => ["image"]
                    ])->get()->each(function ($leave) {
                        $leave->leave_balance = LeaveBalance::where(
                            [
                                "leave_type_id" => $leave->leave_type_id,
                                "assigned_to" => $leave->relationLoaded("requestedBy") ? $leave->requestedBy->id : 0
                            ]
                        )->first();
                    });
                    return response()->json(["leaves" => $leaves]);
                case "performances":
                    $performances = UserPerformanceReview::with([
                        "performanceReview",
                        "assignedTo" => ["image"],
                        "assignedBy" => ["image"]
                    ])->get();

                    return response()->json(["performances" => $performances]);

                case "trainings":
                    $trainings = UserTraining::with([
                        "training",
                        "assignedTo" => ["image"],
                        "assignedBy" => ["image"],
                    ])->get();

                    return response()->json(["trainings" => $trainings]);

                default:
                    $users = User::with(["image"])->get();

                    return response()->json(["users" => $users]);
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
    public function show(User $user)
    {
        try {

            $user->load("image");

            $onboardings = UserOnboarding::with(["onboarding", "assignedBy"])
                ->where("user_onboardings.assigned_to", "=", $user->id)
                ->get();

            // leave balances

            $leaveBalances = LeaveBalance::with(["leave", "providedBy"])
                ->where("leave_balances.assigned_to", "=", $user->id)
                ->get();

            $leaveRequests = LeaveRequest::with(["leave", "requestedBy"])
                ->where("leave_requests.requested_by", "=", $user->id)
                ->get();

            $performanceReviews = UserPerformanceReview::with(["performanceReview", "assignedBy"])
                ->where("user_performance_reviews.assigned_to", "=", $user->id)
                ->get();

            $trainings = UserTraining::with(["training", "assignedBy"])
                ->where("user_trainings.assigned_to", "=", $user->id)
                ->get();

            return response()
                ->json(
                    [
                        "employee" => $user,
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
