<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function store()
    {
        try {
            $attendanceAttr = [
                "user_id" => Auth::id(),
                "login_time" => Carbon::now()
            ];

            $loggedAttendance = Attendance::create($attendanceAttr);

            return response()->json(["success" => !empty($loggedAttendance)]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($requestDate)
    {
        try {
            $parsedDate = Carbon::parse($requestDate);
            $currentDate = $parsedDate->copy()->startOfDay()->format("Y-m-d H:i:s");
            $tomorrowDate = $parsedDate->copy()->addDay()->startOfDay()->format("Y-m-d H:i:s");
            $lateThreshold = $parsedDate->copy()->startOfDay()->addHours(6)->format("Y-m-d H:i:s");
            $user = Auth::id();

            $attendance = [
                "attendance_id" => null,
                "login_time" => null,
                "logout_time" => null,
                "late" => true,
                "absent" => true
            ];

            $log = DB::table("attendances")
                    ->where(function($query) use($currentDate, $tomorrowDate) {
                        $query->where("login_time", ">=", $currentDate)
                                ->where("login_time", "<", $tomorrowDate);
                    })
                    ->where(function($query) use($currentDate, $tomorrowDate) {
                        $query->where(function ($query2) use ($currentDate, $tomorrowDate) {
                            $query2->where("logout_time", ">=", $currentDate)
                                    ->where("logout_time", "<", $tomorrowDate);
                        })
                        ->orWhereNull("logout_time");
                    })
                    ->where("user_id", "=", $user)
                    ->first();

            if (!empty($log)) {
                $attendance["attendance_id"] = $log->id;
                $attendance["login_time"] = $log->login_time;
                $attendance["logout_time"] = $log->logout_time;
                $attendance["late"] = $log->login_time > $lateThreshold;
                $attendance["absent"] = false;
            }

            return response()->json(["attendance" => $attendance]);

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
    public function update(Attendance $attendance)
    {
        try {
            $updated = $attendance->update(["logout_time" => Carbon::now()]);

            return response()->json(["success" => !empty($updated)]);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
