<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{

    ############
    # ASSIGNED #
    ############

    /**
     * Update the specified resource in storage.
     */
    public function assignedUpdate(Request $request, LeaveRequest $leaveRequest)
    {
        try {

            $attributes = $request->validate([
                "approved" => ["required", "boolean"]
            ]);

            $status = $attributes["approved"] ? "approved" : "rejected";

            $updated = $leaveRequest->update(["status" => $status, "actioned_by" => Auth::id()]);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    ############
    # RESOURCE #
    ############

    /**
     * Display a listing of the resource.
     */
    public function resourceIndex()
    {
        try {

            $user = Auth::id();

            $leaveRequests = LeaveRequest::with(["leave", "actionedBy"])
                ->where("requested_by", "=", $user)
                ->whereFuture("end_date")
                ->get();

            return response()->json(["leaves" => $leaveRequests]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {

        try {
            $attributes = $request->validate([
                "start_date" => ["required", "date"],
                "end_date" => ["required", "date"],
                "reason" => ["required", "string"],
                "leave_type_id" => ["required", "integer", "exists:leave_types,id"]
            ]);

            $attributes["requested_by"] = Auth::id();
            $attributes["status"] = "pending";
            $attributes["start_date"] = Carbon::parse($attributes["start_date"])->toDateTimeString();
            $attributes["end_date"] = Carbon::parse($attributes["end_date"])->toDateTimeString();

            $created = LeaveRequest::create($attributes);

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function resourceShow(LeaveRequest $leaveRequest)
    {
        try {
            return response()->json(["leave" => $leaveRequest]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, LeaveRequest $leaveRequest)
    {
        try {

            $attributes = $request->validate([
                "start_date" => ["required", "string", "date"],
                "end_date" => ["required", "string", "date"],
                "reason" => ["required", "string"]
            ]);

            $updated = $leaveRequest->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function resourceDestroy(LeaveRequest $leaveRequest)
    {
        try {
            return response()->json(["success" => $leaveRequest->delete()]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }


}
