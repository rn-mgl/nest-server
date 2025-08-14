<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveBalanceController extends Controller
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

            $searchKey = $attributes["searchKey"];
            $searchValue = $attributes["searchValue"] ?? "";
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $sortKey = $attributes["sortKey"];

            $user = Auth::id();

            $leaveBalances = DB::table("leave_balances as lb")
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("u.id", "=", "lb.provided_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->join("leave_types as lt", function(JoinClause $join) {
                                $join->on("lt.id", "=", "lb.leave_type_id")
                                ->where("lt.is_deleted", "=", false);
                            })
                            ->where("lb.user_id", "=", $user)
                            ->where("{$searchKey}", "LIKE", "%{$searchValue}%")
                            ->orderBy("{$sortKey}", "{$sortType}")
                            ->select([
                                'lb.id as leave_balance_id',
                                'lb.balance',
                                'lt.id as leave_type_id',
                                'lt.type',
                                'lt.description',
                                'lt.created_by',
                                'u.id as user_id',
                                'u.first_name',
                                'u.last_name',
                                'u.email',
                                'u.email_verified_at',
                                'u.created_at',
                            ])
                            ->get();

        return response()->json(["leave_balances" => $leaveBalances]);

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
