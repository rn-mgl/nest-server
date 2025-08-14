<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\LeaveRequest;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest, CategoryRequest $categoryRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $categoryAttributes = $categoryRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes, $categoryAttributes);

            $searchKey = $searchAttributes["searchKey"];
            $searchValue = $searchAttributes["searchValue"] ?? "";

            $categoryKey = $categoryAttributes["categoryKey"];
            $categoryValue = $categoryAttributes["categoryValue"] === "all" ? "" : $categoryAttributes["categoryValue"];

            $sortKey = $sortAttributes["sortKey"];
            $isAsc = filter_var($sortAttributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";

            $user = Auth::id();

            $leaveRequests = DB::table("leave_requests as lr")
                            ->select([
                                "lr.id as leave_request_id",
                                "lr.approved_by",
                                "lr.start_date",
                                "lr.end_date",
                                "lr.reason",
                                "lr.status",
                                "lr.user_id",
                                "lr.created_at as requested_at",
                                "lt.id as leave_type_id",
                                "lt.type",
                                "lt.description",
                            ])
                            ->join("leave_types as lt", function (JoinClause $join) {
                                $join->on("lt.id", "=", "lr.leave_type_id")
                                ->where("lt.is_deleted", "=", false);
                            })
                            ->where("lr.is_deleted", "=", false)
                            ->where("lr.user_id", "=", $user)
                            ->where($searchKey, "LIKE", "%{$searchValue}%")
                            ->where($categoryKey, "LIKE", "%{$categoryValue}%")
                            ->orderBy("lr.{$sortKey}", $sortType)
                            ->get();

            return response()->json(["requests" => $leaveRequests]);

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
                "start_date" => ["required", "date"],
                "end_date" => ["required", "date"],
                "reason" => ["required", "string"],
                "leave_type_id" => ["required", "integer", "exists:leave_types,id"]
            ]);

            $attributes["user_id"] = Auth::id();
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
    public function show(LeaveRequest $leave_request)
    {
        try {
            return response()->json(["request" => $leave_request]);
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
    public function update(Request $request, LeaveRequest $leave_request)
    {
        try {

            $attributes = $request->validate([
                "start_date" => ["required", "string", "date"],
                "end_date" => ["required", "string", "date"],
                "reason" => ["required", "string"]
            ]);

            $updated = $leave_request->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leave_request)
    {
        try {

            $deleted = $leave_request->update(["is_deleted" => true]);

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
