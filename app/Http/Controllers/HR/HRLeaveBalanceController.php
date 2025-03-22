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

class HRLeaveBalanceController extends Controller
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
                    ->leftJoin("leave_balances as lb", function(JoinClause $join) use($attributes) {
                        $join->on("u.id", "=", "lb.user_id")
                        ->where("lb.leave_type_id", "=", $attributes["leave_type_id"]);
                    })
                    ->where("u.is_deleted", "=", false)
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
                        "lb.is_deleted"
                    ])
                    ->get()
                    ->map(function($leave) {
                        if ($leave->is_deleted) {
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
                "employee_ids" => ["array"],
                "employee_ids.*" => ["integer", "exists:users,id"],
                "employee_leaves" => ["array"],
                "employee_leaves.*.user_id" => ["integer", "exists:users,id"],
                "employee_leaves.*.balance" => ["integer"],
                "leave_type_id" => ["required", "integer", "exists:leave_types,id"]
            ]);

            $leaveBalances = DB::table("leave_balances as lb")
                            ->where("leave_type_id", "=", $attributes["leave_type_id"])
                            ->select([
                                "id as leave_balance_id",
                                "user_id",
                                "balance",
                                "is_deleted"
                            ])
                            ->get()
                            ->keyBy("user_id");

            $alreadyAssigned = $leaveBalances->pluck("user_id")->toArray();

            $leave_type_id = $attributes["leave_type_id"];

            // assign
            foreach ($attributes["employee_ids"] as $employee) {
                if (!in_array($employee, $alreadyAssigned)) {
                    $leaveBalanceAttr = [
                        "user_id" => $employee,
                        "provided_by" => Auth::guard("base")->id(),
                        "leave_type_id" => $leave_type_id,
                        "balance" => 0
                    ];
                    $assigned = LeaveBalance::create($leaveBalanceAttr);

                    // make std class for uniformity in $leaveBalances collection
                    $newLeaveBalance = new stdClass();
                    $newLeaveBalance->leave_balance_id = $assigned->id;
                    $newLeaveBalance->user_id = $employee;
                    $newLeaveBalance->balance = 0;
                    $leaveBalances->put($employee, $newLeaveBalance);
                }
            }

            // delete
            foreach($alreadyAssigned as $id) {
                if (!in_array($id, $attributes["employee_ids"])) {
                    $leaveBalanceId = $leaveBalances->get($id);
                    $leaveBalance = LeaveBalance::find($leaveBalanceId->leave_balance_id);
                    $deleted = $leaveBalance->update(["is_deleted" => true]);
                    // remove user in leaveBalances
                    $leaveBalances->forget($id);
                }
            }

            // update balance of assigned
            // check employee_leaves array if the id is in leaveBalances and update the applied balance
            foreach($attributes["employee_leaves"] as $leaves) {
                $currUser = $leaves["user_id"];
                if ($leaveBalances->has($currUser)) {
                    $balance = $leaveBalances->get($currUser);
                    $leaveBalance = LeaveBalance::find($balance->leave_balance_id);
                    $updated = $leaveBalance->update([
                        "balance" => $leaves["balance"],
                        "is_deleted" => false
                    ]);
                }
            }

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
