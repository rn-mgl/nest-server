<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
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
    public function index()
    {
        try {

            $leaves = LeaveType::with(["createdBy"])->get();

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

            LeaveType::create($attributes);

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

            $deleted = DB::transaction(function () use ($leaveType) {

                $deleted = $leaveType->delete();

                $deletedLeaveRequests = LeaveRequest::where("leave_type_id", "=", $leaveType->id)->delete();

                $deletedLeaveBalances = LeaveBalance::where("leave_type_id", "=", $leaveType->id)->delete();

                return $deleted || $deletedLeaveRequests || $deletedLeaveBalances;

            });

            return response()->json(["success" => $deleted]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
