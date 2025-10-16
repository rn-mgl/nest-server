<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Document;
use App\Models\Folder;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Models\UserPerformanceReview;
use App\Models\UserTraining;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index()
    {

        try {

            $userRoles = Auth::user()->roles;

            switch (true) {
                case $userRoles->contains("role", "admin"):
                    return $this->adminDashboard();
                case $userRoles->contains("role", "hr");
                    return $this->hrDashboard();
                case $userRoles->contains("role", "employee");
                    return $this->employeeDashboard();
                default:
                    throw new Exception("You are not authorized to view the dashboard.");
            }

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    private function adminDashboard()
    {
        try {

            $hrs = User::ofRole(["hr"])->with(["image"])->get();

            return response()->json(["hrs" => $hrs]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    private function hrDashboard()
    {
        try {

            $users = User::get();

            $attendances = Attendance::whereToday("login_time")
                ->where(function (Builder $query) {
                    $query->whereToday("logout_time")->orWhereNull("logout_time");
                })
                ->get();

            $userIds = $users->pluck("id");
            $attendanceUsers = $attendances->pluck("user_id");

            $lateThreshold = Carbon::now()->startOfDay()->addHours(6);

            $lates = $attendances->filter(fn($attendance) => Carbon::parse($attendance->login_time)->greaterThan($lateThreshold))->count();
            $outs = $attendances->whereNotNull("logout_time")->count();
            $absents = $userIds->diff($attendanceUsers)->count();

            $attendanceStatus = [
                "in" => $attendances->count(),
                "out" => $outs,
                "late" => $lates,
                "absent" => $absents
            ];

            $onboardings = UserOnboarding::all()
                ->groupBy("status")
                ->map(fn($onboarding) => $onboarding->count());

            $leaves = LeaveRequest::all()
                ->groupBy("status")
                ->map(fn($leave) => $leave->count());

            $performances = UserPerformanceReview::all()
                ->groupBy("status")
                ->map(fn($performance) => $performance->count());

            $trainings = UserTraining::all()
                ->groupBy("status")
                ->map(fn($training) => $training->count());

            $documents = Document::count();

            $folders = Folder::count();

            $documentAndFolders = [
                'documents' => $documents,
                'folders' => $folders
            ];

            return response()->json(
                [
                    "users" => $users->count(),
                    "attendances" => $attendanceStatus,
                    "onboardings" => $onboardings,
                    "leaves" => $leaves,
                    "performances" => $performances,
                    "trainings" => $trainings,
                    "documents" => $documentAndFolders
                ]
            );

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    private function employeeDashboard()
    {
        try {

            $id = Auth::id();

            $user = User::findOrFail($id);

            $attendance = Attendance::where("user_id", "=", $id)
                ->whereToday("login_time")
                ->where(function ($query) {
                    $query->whereToday("logout_time")
                        ->orWhereNull("logout_time");
                })
                ->first();

            $lateThreshold = Carbon::now()->startOfDay()->addHours(6);
            $late = Carbon::now()->greaterThan($lateThreshold);
            $login_time = $attendance?->login_time;

            if ($login_time) {
                $late = Carbon::parse($login_time)->greaterThan($lateThreshold);
            }

            $attendances = [
                "in" => $login_time,
                "out" => $attendance?->logout_time,
                "late" => $late,
                "absent" => empty($attendance)
            ];

            $onboardings = UserOnboarding::where("assigned_to", "=", $user)
                ->get()
                ->groupBy("status")
                ->map(fn($onboarding) => $onboarding->count());

            $leaves = LeaveRequest::where("requested_by", "=", $user)
                ->get()
                ->groupBy("status")
                ->map(fn($leave) => $leave->count());

            $performances = UserPerformanceReview::where("assigned_to", "=", $user)
                ->get()
                ->groupBy("status")
                ->map(fn($performance) => $performance->count());

            $trainings = UserTraining::where("assigned_to", "=", $user)
                ->get()
                ->groupBy("status")
                ->map(fn($training) => $training->count());

            $documents = Document::all()->count();

            $folders = Folder::all()->count();

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

}
