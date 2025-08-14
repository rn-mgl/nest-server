<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\Folder;
use App\Models\EmployeeOnboarding;
use App\Models\EmployeePerformanceReview;
use App\Models\EmployeeTraining;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $users = User::where("is_deleted", false)->with("roles")->get();

            $attendances = Attendance::where(function(Builder $query) {
                                $query->whereToday("login_time");
                            })
                            ->where(function (Builder $query) {
                                $query->where(function (Builder $query2) {
                                    $query2->whereToday("logout_time")
                                    ->whereColumn("logout_time", ">", "login_time");
                                })->orWhereNull("logout_time");
                            })
                            ->get();

            /**
             * Logic
             *
             * in: if has login and is less than 6
             * late: login is greater than today's 6 AM
             *
             */

            $userIds = $users->pluck("id")->toArray();
            $attendanceUsers = $attendances->pluck("user_id")->toArray();

            $lates = 0;
            $outs = 0;
            $absents = array_diff($userIds, $attendanceUsers);

            foreach ($attendances as $attendance) {

                if (!empty($attendance->login_time)) {
                    $isLate = Carbon::parse($attendance->login_time)->greaterThan(Carbon::now()->startOfDay()->addHours(6));
                    $lates = $isLate ? $lates + 1 : $lates;
                }

                if (!empty($attendance->logout_time)) {
                    $outs += 1;
                }

            }

            $attendanceStatus = [
                "in" => $attendances->count(),
                "out" => $outs,
                "late" => $lates,
                "absent" => count($absents)
            ];

            $onboardings = EmployeeOnboarding::where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn($onboarding) => $onboarding->count());

            $leaves = LeaveRequest::where("is_deleted", "=", false)
                        ->get()
                        ->groupBy("status")
                        ->map(fn ($leave) => $leave->count());

            $performances = EmployeePerformanceReview::where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn ($performance) => $performance->count());

            $trainings = EmployeeTraining::where("is_deleted", "=", false)
                            ->get()
                            ->groupBy("status")
                            ->map(fn ($training) => $training->count());

            $documents = Document::where("is_deleted", "=", false)->get()->count();

            $folders = Folder::where("is_deleted", "=", false)->get()->count();

            $users = $users->groupBy(fn($user) => $user->roles->role)->map(fn($user) => $user->count());

            $documentAndFolders = [
                'documents' => $documents,
                'folders' => $folders
            ];

            return response()->json(
                [
                    "users" => $users,
                    "attendances" => $attendanceStatus,
                    "onboardings" => $onboardings,
                    "leaves" => $leaves,
                    "performances" => $performances,
                    "trainings" => $trainings,
                    "documents" => $documentAndFolders
                ]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**f
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
