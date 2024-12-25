<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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
            $currentDate = $parsedDate->startOfDay()->format("Y-m-d H:i:s");
            $tomorrowDate = $parsedDate->addDay()->startOfDay()->format("Y-m-d H:i:s");
            $attendances = DB::table("users as u")
                            ->leftJoin("attendances as a", function(JoinClause $join) use($currentDate, $tomorrowDate) {
                                $join->on("a.user_id", "=", "u.id")
                                ->where("a.login_time", ">=", $currentDate)
                                ->where("a.logout_time", "<", $tomorrowDate);
                            })
                            ->where("u.is_deleted", "=", false)
                            ->select(
                                DB::raw("COUNT(CASE WHEN a.login_time IS NOT NULL THEN 1 END) as ins"), // in count
                                DB::raw("COUNT(CASE WHEN a.logout_time IS NOT NULL THEN 1 END) as outs"), // out count
                                DB::raw("COUNT(CASE WHEN a.login_time IS NOT NULL AND TIME(a.login_time) > '06:00:00' THEN 1 END) as lates"), // late count (login > 6:00)
                                DB::raw("COUNT(CASE WHEN a.login_time IS NULL AND a.logout_time IS NULL AND TIME(NOW()) > '06:00:00' THEN 1 END) as absents"), // absent count (no in and no out)
                            )
                            ->first();

            return response()->json(["attendances" => $attendances]);
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
            $parsedDate = Carbon::parse($requestDate);
            $currentDate = $parsedDate->startOfDay()->format("Y-m-d H:i:s");
            $tomorrowDate = $parsedDate->addDay()->startOfDay()->format("Y-m-d H:i:s");
            $attendances = DB::table("users as u")
                            ->leftJoin("attendances as a", function(JoinClause $join) use($currentDate, $tomorrowDate) {
                                $join->on("a.user_id", "=", "u.id")
                                ->where("a.login_time", ">=", $currentDate)
                                ->where("a.logout_time", "<", $tomorrowDate);
                            })
                            ->where("u.is_deleted", "=", false)
                            ->select([
                                "u.id as user_id",
                                "a.id as attendance_id",
                                "a.login_time",
                                "a.logout_time",
                                "u.first_name",
                                "u.last_name",
                                DB::raw("CASE WHEN a.login_time IS NOT NULL AND TIME(a.login_time) > '6:00:00' OR a.login_time IS NULL AND TIME(NOW()) > '6:00:00' THEN TRUE ELSE FALSE END AS late"),
                                DB::raw("CASE WHEN a.login_time IS NULL AND a.logout_time IS NULL AND TIME(NOW()) > '6:00:00' THEN TRUE ELSE FALSE END AS absent")
                            ])
                            ->get();

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
