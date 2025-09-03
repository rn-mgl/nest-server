<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Broadcasters\NullBroadcaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

            $parsedDate = Carbon::create($attributes['currentYear'], $attributes['currentMonth'], $attributes['currentDate'])->startOfDay();
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($requestDate)
    {
        try {
            $parsedDate = Carbon::parse($requestDate)->startOfDay();
            $lateThreshold = $parsedDate->copy()->addHours(6);

            $attendances = User::with(
                [
                    "attendances" => fn($query) => $query->whereDate("login_time", "=", $parsedDate)
                ]
            )
                ->get()
                ->map(function ($user) use ($lateThreshold) {
                    $attendance = $user->attendances->first();
                    $user->unsetRelation('attendances');

                    if (!$attendance) {
                        $user->attendance = [
                            'id' => null,
                            'user_id' => $user->id,
                            'login_time' => null,
                            'logout_time' => null,
                            'late' => null,
                            'absent' => true
                        ];

                        return $user;
                    }

                    $attendance->late = Carbon::parse($attendance->login_time)->greaterThan($lateThreshold);
                    $attendance->absent = false;

                    $user->attendance = $attendance;

                    return $user;
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
