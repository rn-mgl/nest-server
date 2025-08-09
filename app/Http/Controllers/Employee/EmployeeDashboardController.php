<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\EmployeeOnboarding;
use App\Models\EmployeePerformanceReview;
use App\Models\EmployeeTraining;
use App\Models\LeaveRequest;
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

            $user = Auth::id();

            $attendance = Attendance::where("user_id", "=", $user)
                            ->whereToday("login_time")
                            ->where(function($query) {
                                $query->whereToday("logout_time")
                                ->orWhereNull("logout_time");
                            })
                            ->first();

            $late = Carbon::now()->greaterThan(Carbon::now()->startOfDay()->addHours(6));
            $login_time = $attendance->login_time ?? null;

            if ($login_time) {
                $late = Carbon::parse($login_time)->greaterThan(Carbon::now()->startOfDay()->addHours(6));
            }

            $attendances = [
                "in" => $attendance->login_time !== null,
                "out" => $attendance->logout_time !== null,
                "late" => $late,
                "absent" => empty($attendance)
            ];

            $onboardings = EmployeeOnboarding::where("employee_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($onboarding) => $onboarding->count());

            $leaves = LeaveRequest::where("user_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($leave) => $leave->count());

            $performances = EmployeePerformanceReview::where("employee_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($performance) => $performance->count());

            $trainings = EmployeeTraining::where("employee_id", "=", $user)
                            ->where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($training) => $training->count());

            $documents = Document::where("is_deleted", "=", false)->count();

            $folders = DocumentFolder::where("is_deleted", "=", false)->count();

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
