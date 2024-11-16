<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $attributes = $request->validate([
                "searchKey" => ["required", "string"],
                "searchValue" =>["nullable", "string"],
                "sortKey" => ["required", "string"],
                "isAsc" => ["required", "string"],
            ]);

            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? "";

            $leaves = DB::table("leave_types")
                    ->join("users", function (JoinClause $join) {
                        $join->on("users.id", "=", "leave_types.created_by")
                    ->where("users.is_deleted", "=", false);
                    })
                    ->where("leave_types.is_deleted", "=", false)
                    ->where($attributes["searchKey"], "LIKE", "%{$searchValue}%")
                    ->select(
                [
                            "type",
                            "description",
                            "leave_types.id",
                            "leave_types.created_at",
                            "leave_types.created_by",
                            "users.first_name",
                            "users.last_name",
                            "users.email",
                        ]
                    )
                    ->orderBy($attributes["sortKey"], $sortType)
                    ->get();

            return response()->json(["leaves" => $leaves]);
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
        try {
            $attributes = $request->validate([
                "type" => ["required", "string", "unique:leave_types,type"],
                "description" => ["required", "string"],
            ]);

            $attributes["created_by"] = Auth::id();

            $leaveType = LeaveType::create($attributes);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        //
    }
}
