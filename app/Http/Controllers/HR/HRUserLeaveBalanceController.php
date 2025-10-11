<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\User;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class HRUserLeaveBalanceController extends Controller
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

            $users = User::with(
                [
                    "assignedLeaveBalances" => function ($query) use ($attributes) {
                        $query->where("leave_type_id", "=", $attributes["leave_type_id"])
                            ->withTrashed();
                    },
                    "assignedLeaveBalances.leave",
                    "image"
                ]
            )->ofRole("employee")
                ->get()
                ->each(function ($user) {
                    if ($user->relationLoaded("assignedLeaveBalances")) {
                        $user->assigned_leave_balance = $user->assignedLeaveBalances->first();
                        $user->unsetRelation("assignedLeaveBalances");
                    }
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
                "user_leaves.*.id" => ["integer", "exists:users,id"],
                "user_leaves.*.assigned_leave_balance.balance" => ["sometimes", "integer"],
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
                    ->keyBy("assigned_to");

                $alreadyAssigned = $leaveBalances->keys();

                // ticked user ids without any db records yet
                $newlyAssigned = $checkedUserIds->diff($alreadyAssigned);

                // if the user id is not yet assigned, create new leave balance record
                foreach ($newlyAssigned as $user) {
                    $leaveBalances->put($user, LeaveBalance::create([
                        "assigned_to" => $user,
                        "provided_by" => Auth::id(),
                        "leave_type_id" => $leaveTypeId,
                        "balance" => 0
                    ]));
                }

                // already assigned users that are unchecked
                $revokedUsers = $alreadyAssigned->diff($checkedUserIds);

                // soft delete revoked users
                LeaveBalance::whereIn("assigned_to", $revokedUsers)
                    ->where("leave_type_id", $leaveTypeId)
                    ->delete();

                // update balance of assigned
                foreach ($usersLeaveDetails as $leave) {
                    // check if the user id is a key in leaveBalances and update the applied balance
                    $leaveBalance = $leaveBalances->get($leave["id"]);
                    // check if $leave["assigned_leave_balance"]["balance"] is set because it is possible that the user only has assigned leave but no balance
                    if ($leaveBalance && isset($leave["assigned_leave_balance"]["balance"])) {
                        // restore the record only if the user leave record is in the array of user ids (checked users)
                        $leaveBalance->update([
                            "balance" => $leave["assigned_leave_balance"]["balance"],
                            "deleted_at" => $checkedUserIds->contains($leave["id"]) ? null : $leaveBalance->deleted_at
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
