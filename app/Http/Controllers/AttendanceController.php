<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {



        } catch (\Throwable $th) {
            //throw $th;
        }
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
            throw new Exception($th->getMessage());
        }
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
