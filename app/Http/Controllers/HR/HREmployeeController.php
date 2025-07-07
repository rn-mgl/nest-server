<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HREmployeeController extends Controller
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

            $attributes = array_merge($searchAttributes, $sortAttributes, $categoryAttributes,);

            $verified = filter_var($attributes["categoryValue"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? "";

            $employees = DB::table("users as u")
                        ->where("role", "=", "employee")
                        ->when($verified === true, fn($query) => $query->whereNotNull("email_verified_at"))
                        ->when($verified === false, fn($query) => $query->whereNull("email_verified_at"))
                        ->whereLike($attributes["searchKey"], "%$searchValue%")
                        ->select([
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "u.image",
                            "u.email_verified_at",
                            "u.created_at",
                        ])
                        ->orderBy($attributes["sortKey"], $sortType)
                        ->get();

            return response()->json(["employees" => $employees]);

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
