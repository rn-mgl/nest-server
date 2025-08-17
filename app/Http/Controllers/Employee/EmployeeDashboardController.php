<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\Folder;
use App\Models\UserOnboarding;
use App\Models\UserPerformanceReview;
use App\Models\UserTraining;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $id = Auth::id();

            $user = User::findOrFail($id);

            $attendance = Attendance::where("user_id", "=", $id)
                            ->whereToday("login_time")
                            ->where(function($query) {
                                $query->whereToday("logout_time")
                                ->orWhereNull("logout_time");
                            })
                            ->first();

            $late = Carbon::now()->greaterThan(Carbon::now()->startOfDay()->addHours(6));
            $login_time = $attendance?->login_time;

            if ($login_time) {
                $late = Carbon::parse($login_time)->greaterThan(Carbon::now()->startOfDay()->addHours(6));
            }

            $attendances = [
                "in" => $login_time,
                "out" => $attendance?->logout_time,
                "late" => $late,
                "absent" => empty($attendance)
            ];

            $onboardings = $user->assignedOnboardings()->get();

            $leaves = $user->leaveRequests()->get();

            $performances = UserPerformanceReview::where("employee_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($performance) => $performance->count());

            $trainings = UserTraining::where("employee_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($training) => $training->count());

            $documents = Document::where("is_deleted", "=", false)->count();

            $folders = Folder::where("is_deleted", "=", false)->count();

            $documentsAndFolders = [
                "documents" => $documents,
                "folders" => $folders
            ];

            return response()->json([
                "attendances" => $attendances,
                "onboardings" => $onboardings,
                "leaves" => $leaves,
                "performances" => $performances,
                "trainings" => $trainings,
                "documents" => $documentsAndFolders
            ]);

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
