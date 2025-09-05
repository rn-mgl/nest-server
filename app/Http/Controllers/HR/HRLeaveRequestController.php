<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRLeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = Auth::id();

            $requests = LeaveRequest::with(["leave"])->where("requested_by", "=", $user)->get();

            return response()->json(["requests" => $requests]);

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
                "start_date" => ["required", "string", "date"],
                "end_date" => ["required", "string", "date"],
                "reason" => ["required", "string"],
                "leave_type_id" => ["required", "exists:leave_types,id"]
            ]);

            $attributes["user_id"] = Auth::id();
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
    public function show(LeaveRequest $leaveRequest)
    {
        try {

            return response()->json(["request" => $leaveRequest]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        try {

            $attributes = $request->validate([
                "start_date" => ["required", "string", "date"],
                "end_date" => ["required", "string", "date"],
                "reason" => ["required", "string"]
            ]);

            $attributes["start_date"] = Carbon::parse($attributes["start_date"])->toDateTimeString();
            $attributes["end_date"] = Carbon::parse($attributes["end_date"])->toDateTimeString();

            $updated = $leaveRequest->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        try {

            return response()->json(["success" => $leaveRequest->delete()]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
