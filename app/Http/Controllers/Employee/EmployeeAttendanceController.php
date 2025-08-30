<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

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
            $currentDate = $parsedDate->format("Y-m-d H:i:s");
            $lateThreshold = $parsedDate->copy()->addHours(6)->format("Y-m-d H:i:s");

            $user = Auth::id();

            $attendance = [
                "attendance_id" => null,
                "login_time" => null,
                "logout_time" => null,
                "late" => true,
                "absent" => true
            ];

            $log = Attendance::where("user_id", "=", $user)
                ->whereDate("login_time", $currentDate)
                ->where(fn($query) => $query->whereDate("logout_time", $currentDate)->orWhereNull("logout_time"))
                ->first();

            if ($log) {
                $attendance = [
                    "attendance_id" => $log->id,
                    "login_time" => $log->login_time,
                    "logout_time" => $log->logout_time,
                    "late" => Carbon::parse($log->login_time)->greaterThan($lateThreshold),
                    "absent" => false
                ];
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

            return response()->json(["success" => $updated]);

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
