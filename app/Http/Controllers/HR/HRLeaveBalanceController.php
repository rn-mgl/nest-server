<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRLeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $searchKey = $searchAttributes["searchKey"];
            $searchValue = $searchAttributes["searchValue"] ?? "";

            $sortKey = $sortAttributes["sortKey"];
            $isAsc = filter_var($sortAttributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";

            $user = Auth::id();

            $balances = DB::table("leave_balances as lb")
                        ->select([
                            "lb.id as leave_balance_id",
                            "lb.balance",
                            "lt.id as leave_type_id",
                            "lt.type",
                            "lt.description"
                        ])
                        ->join("leave_types as lt", function (JoinClause $join) {
                            $join->on("lt.id", "=", "lb.leave_type_id");
                        })
                        ->where("lb.user_id", "=", $user)
                        ->where($searchKey, "LIKE", "%{$searchValue}%")
                        ->orderBy($sortKey, $sortType)
                        ->get();

            return response()->json(["balances" => $balances]);

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
