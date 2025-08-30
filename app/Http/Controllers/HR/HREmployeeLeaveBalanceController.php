<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class HREmployeeLeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "leave_type_id" => ["required", "integer"]
            ]);

            $users = DB::table("users as u")
                ->leftJoin("leave_balances as lb", function (JoinClause $join) use ($attributes) {
                    $join->on("u.id", "=", "lb.assigned_to")
                        ->where("lb.leave_type_id", "=", $attributes["leave_type_id"]);
                })
                ->select([
                    "u.id as user_id",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    "u.email_verified_at",
                    "u.created_at",
                    "lb.id as leave_balance_id",
                    "lb.leave_type_id",
                    "lb.balance",
                    "lb.deleted_at"
                ])
                ->get()
                ->map(function ($leave) {
                    // the leave is not assigned if it is deleted
                    if ($leave->deleted_at) {
                        $leave->leave_balance_id = null;
                    }
                    return $leave;
                });

            return response()->json(["users" => $users]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
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
            $attributes = $request->validate([
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "user_leaves" => ["array", "required"],
                "user_leaves.*.user_id" => ["integer", "exists:users,id"],
                "user_leaves.*.balance" => ["integer", "required"],
                "leave_type_id" => ["required", "integer", "exists:leave_types,id"]
            ]);

            // the leave type to be assigned
            $leaveTypeId = $attributes["leave_type_id"];
            // the checked user ids
            $checkedUserIds = collect($attributes["user_ids"] ?? []);
            // all leave balances deleted or not, contains user_id and balance_id after validation
            $usersLeaveDetails = collect($attributes["user_leaves"] ?? []);

            DB::transaction(function () use ($leaveTypeId, $checkedUserIds, $usersLeaveDetails) {

                $leaveBalances = LeaveBalance::withTrashed()
                    ->where("leave_type_id", "=", $leaveTypeId)
                    ->get()
                    ->keyBy("user_id");

                $alreadyAssigned = $leaveBalances->keys();

                // ticked user ids without any db records yet
                $newUsers = $checkedUserIds->diff($alreadyAssigned);

                // if the user id is not yet assigned, create new leave balance record
                foreach ($newUsers as $user) {
                    $leaveBalances->put($user, LeaveBalance::create([
                        "user_id" => $user,
                        "provided_by" => Auth::id(),
                        "leave_type_id" => $leaveTypeId,
                        "balance" => 0
                    ]));
                }

                // already assigned users that are unchecked
                $revokedUsers = $alreadyAssigned->diff($checkedUserIds);

                // soft delete revoked users
                $deleted = LeaveBalance::whereIn("user_id", $revokedUsers)
                    ->where("leave_type_id", $leaveTypeId)
                    ->delete();

                // update balance of assigned
                foreach ($usersLeaveDetails as $leave) {
                    // check if the user id is a key in leaveBalances and update the applied balance
                    $leaveBalance = $leaveBalances->get($leave["user_id"]);
                    if ($leaveBalance) {
                        // restore the record only if the user leave record is in the array of user ids (checked users)
                        $leaveBalance->update([
                            "balance" => $leave["balance"],
                            "deleted_at" => $checkedUserIds->contains($leave["user_id"]) ? null : $leaveBalance->deleted_at
                        ]);
                    }
                }

            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
