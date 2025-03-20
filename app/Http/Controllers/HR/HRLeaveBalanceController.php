<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                        ->where("lb.leave_type_id", "=", $attributes["leave_type_id"])
                        ->where("lb.is_deleted", "=", false);
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
                        "lb.balance"
                    ])
                    ->get();

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
        //
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
