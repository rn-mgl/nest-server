<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "currentDate" => ["required", "integer"],
                "currentMonth" => ["required", "integer"],
                "currentYear" => ["required", "integer"],
            ]);

            $dateString = "{$attributes['currentYear']}-{$attributes['currentMonth']}-{$attributes['currentDate']}";
            $parsedDate = Carbon::parse($dateString);
            $currentDate = $parsedDate->copy()->startOfDay()->format("Y-m-d H:i:s");
            $tomorrowDate = $parsedDate->copy()->addDay()->startOfDay()->format("Y-m-d H:i:s");
            $latesThreshold = $parsedDate->copy()->startOfDay()->addHours(6)->format("Y-m-d H:i:s");

            // get ins and outs within the day
            $attendances = Attendance::where("login_time", ">=", $currentDate)
                            ->where("login_time", "<", $tomorrowDate)
                            ->get();

            // get all users that logged in
            $ins = $attendances->pluck("user_id")->toArray();
            $lates = 0;
            $outs = 0;

            // process lates and outs
            foreach ($attendances as $a) {
                if ($a->login_time > $latesThreshold) {
                    $lates++;
                }

                if ($a->logout_time >= $currentDate && $a->logout_time < $tomorrowDate) {
                    $outs++;
                }
            }

            $users = User::pluck("id")->toArray();

            // get users that did not log in
            $absents = array_diff($users, $ins);

            $attendance = [
                "ins" => count($ins),
                "outs" => $outs,
                "lates" => $lates,
                "absents" => count($absents)
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($requestDate)
    {
        try {
            $parsedDate = Carbon::parse($requestDate)->startOfDay();
            $currentDate = $parsedDate->copy()->format("Y-m-d H:i:s");
            $lateThreshold = $parsedDate->copy()->addHours(6)->format("Y-m-d H:i:s");

            $ins = Attendance::whereDate("login_time", $currentDate)->get()->keyBy("user_id");

            $users = User::all();

            $attendances = $users->map(function ($user) use ($ins, $lateThreshold) {
                $attendanceData = [
                    "user_id" => $user->id,
                    "first_name" => $user->first_name,
                    "last_name" => $user->last_name,
                    "email" => $user->email,
                    "email_verified_at" => $user->email_verified_at,
                    "created_at" => $user->created_at,
                    "attendance_id" => null,
                    "login_time" => null,
                    "logout_time" => null,
                    "late" => null,
                    "absent" => true,
                ];

                if ($ins->has($user->id)) {
                    $attendance = $ins->get($user->id);

                    $attendanceData["attendance_id"] = $attendance->id;
                    $attendanceData["login_time"] = $attendance->login_time;
                    $attendanceData["logout_time"] = $attendance->logout_time;
                    $attendanceData["late"] = $attendance->login_time > $lateThreshold;
                    $attendanceData["absent"] = false;
                }

                return $attendanceData;
            });

            return response()->json(["attendances" => $attendances]);
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
