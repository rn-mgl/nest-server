<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "activeDate" => ["required", "integer"],
                "activeMonth" => ["required", "integer"],
                "activeYear" => ["required", "integer"],
            ]);

            $parsedDate = Carbon::create($attributes['activeYear'], $attributes['activeMonth'], $attributes['activeDate'])->startOfDay();
            $latesThreshold = $parsedDate->copy()->addHours(6)->format("Y-m-d H:i:s");

            // get logs within the day
            $attendances = Attendance::whereDate("login_time", $parsedDate)
                ->where(function (Builder $query) use ($parsedDate) {
                    $query->whereDate("logout_time", $parsedDate)->orWhereNull("logout_time");
                })
                ->get();

            $ins = $attendances->pluck("user_id");
            $lates = $attendances->filter(fn($attendance) => Carbon::parse($attendance->login_time)->greaterThan($latesThreshold))->count();
            $outs = $attendances->whereNotNull("logout_time")->count();
            $absents = User::pluck("id")->diff($ins)->count();

            $attendance = [
                "ins" => $ins->count(),
                "outs" => $outs,
                "lates" => $lates,
                "absents" => $absents
            ];

            return response()->json(["attendances" => $attendance]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
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
            $attendanceAttr = [
                "user_id" => Auth::id(),
                "login_time" => Carbon::now()
            ];

            $log = Attendance::create($attendanceAttr);

            return response()->json(["success" => $log]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $requestDate)
    {
        try {
            $parsedDate = Carbon::parse($requestDate)->startOfDay();
            $lateThreshold = $parsedDate->copy()->addHours(6);

            $user = Auth::id();

            $attendance = [
                "attendance_id" => null,
                "login_time" => null,
                "logout_time" => null,
                "late" => null,
                "absent" => true,
                "user_id" => Auth::id()
            ];

            $log = Attendance::where("user_id", "=", $user)
                ->whereDate("login_time", $parsedDate)
                ->where(fn($query) => $query->whereDate("logout_time", $parsedDate)->orWhereNull("logout_time"))
                ->first();

            if ($log) {
                $attendance = [
                    "id" => $log->id,
                    "login_time" => $log->login_time,
                    "logout_time" => $log->logout_time,
                    "late" => Carbon::parse($log->login_time)->greaterThan($lateThreshold),
                    "absent" => false,
                    "user_id" => Auth::id()
                ];
            }

            return response()->json(["attendance" => $attendance]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
