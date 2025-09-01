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
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest, CategoryRequest $categoryRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $categoryAttributes = $categoryRequest->validated();

            $searchKey = $searchAttributes["searchKey"];
            $searchValue = $searchAttributes["searchValue"] ?? "";

            $sortKey = $sortAttributes["sortKey"];
            $isAsc = filter_var($sortAttributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";

            $categoryKey = $categoryAttributes["categoryKey"];
            $categoryValue = $categoryAttributes["categoryValue"] === "all" ? "" : $categoryAttributes["categoryValue"];

            $user = Auth::id();

            $requests = DB::table("leave_requests as lr")
                ->select([
                    "lr.id as leave_request_id",
                    "lr.created_at as requested_at",
                    "lr.start_date",
                    "lr.end_date",
                    "lr.status",
                    "lr.reason",
                    "lt.id as leave_type_id",
                    "lt.type",
                    "lt.description"
                ])
                ->join("leave_types as lt", function (JoinClause $join) {
                    $join->on("lt.id", "=", "lr.leave_type_id")
                        ->whereNull("lt.deleted_at");
                })
                ->whereNull("lr.deleted_at")
                ->where("user_id", "=", $user)
                ->where($searchKey, "LIKE", "%{$searchValue}%")
                ->where($categoryKey, "LIKE", "%{$categoryValue}%")
                ->orderBy("lr.{$sortKey}", $sortType)
                ->get();

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

            $deleted = $leaveRequest->delete();

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
