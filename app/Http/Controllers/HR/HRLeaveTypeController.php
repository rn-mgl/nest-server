<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\LeaveType;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRLeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes);

            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? "";

            $leaves = DB::table("leave_types as lt")
                    ->join("users as u", first: function (JoinClause $join) {
                        $join->on("u.id", "=", "lt.created_by")
                        ->where("u.is_deleted", "=", false);
                    })
                    ->where("lt.is_deleted", "=", false)
                    ->where($attributes["searchKey"], "LIKE", "%{$searchValue}%")
                    ->select(
                [
                            "type",
                            "description",
                            "lt.id as leave_id",
                            "lt.created_at",
                            "lt.created_by",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                        ]
                    )
                    ->orderBy($attributes["sortKey"], $sortType)
                    ->get();

            return response()->json(["leaves" => $leaves]);
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
                "type" => ["required", "string"],
                "description" => ["required", "string"],
            ]);

            $attributes["created_by"] = Auth::id();

            $leaveType = LeaveType::create($attributes);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        try {
            return response()->json(["leave" => $leaveType]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
        try {
            $attributes = $request->validate([
                "type" => ["required", "string"],
                "description" => ["required", "string"],
            ]);

            $updatedLeave = $leaveType->update($attributes);

            return response()->json(["success" => $updatedLeave]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        try {
            $deletedLeaveType = $leaveType->update(["is_deleted" => true]);

            return response()->json(["success" => $deletedLeaveType]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
