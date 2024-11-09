<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HREmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "searchKey" => ["required", "string"],
                "searchValue" => ["nullable", "string"], // Allows empty strings without converting to null
                "categoryKey" => ["required", "string"],
                "categoryValue" => ["required", "string"],
                "sortKey" => ["required", "string"],
                "isAsc" => ["required", "string"],
            ]);

            $verified = filter_var($attributes["categoryValue"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? "";

            $employees = DB::table("users")
                        ->where("role", "=", "employee")
                        ->when($verified === true, fn($query) => $query->whereNotNull("email_verified_at"))
                        ->when($verified === false, fn($query) => $query->whereNull("email_verified_at"))
                        ->whereLike($attributes["searchKey"], "%$searchValue%")
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
