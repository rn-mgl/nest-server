<?php

namespace App\Http\Controllers\HR;

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

            $users = User::with("role")->get();

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

            $users = $users->groupBy(fn($user) => $user->role->role)->map(fn($user) => $user->count());

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
                ]
            );

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
