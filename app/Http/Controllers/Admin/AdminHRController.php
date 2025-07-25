<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AdminHRController extends Controller
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

            $categoryValue = $attributes["categoryValue"];

            // Convert category and sort direction values to booleans
            $verified= $categoryValue === "All" ? "" : $categoryValue === "Verified";
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // Determine the sort direction
            $sortType = $isAsc ? "ASC" : "DESC";
            $searchValue = $attributes["searchValue"] ?? ""; // Retain empty string if searchValue is empty

            // Query the 'users' table, applying search and sorting filters
            $hrs = DB::table("users")
                    ->where("role", "hr")
                    ->when($verified === true, fn($query) => $query->whereNotNull("email_verified_at"))
                    ->when($verified === false, fn($query) => $query->whereNull("email_verified_at"))
                    ->whereLike($attributes["searchKey"], "%{$searchValue}%")
                    ->orderBy($attributes["sortKey"], $sortType)
                    ->select([
                        "id as user_id",
                        "first_name",
                        "last_name",
                        "email",
                        "email_verified_at",
                        "created_at"
                    ])
                    ->get();

            return response()->json(["hrs" => $hrs]);
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

        $attributes = $request->validate([
            "type" => ["required", "string"]
        ]);

        try {

            switch ($attributes["type"]) {
                case "deactivate":
                    return $this->deactivate($id);
                case "verify":
                    return $this->verify($id);
                default:
                    throw new Exception("Invalid update action");
            }

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function deactivate(string $id) {
        try {
            $hr = DB::table("users")
                    ->where("role", "=", "hr")
                    ->where("id", "=", $id)
                    ->update(["email_verified_at" => null]);

            return response()->json(["success" => $hr]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    private function verify(string $id) {
        try {
            $hr = DB::table("users")
                    ->where("role", "=", "hr")
                    ->where("id", "=", $id)
                    ->update(["email_verified_at" => Carbon::now()]);

            return response()->json(["success" => $hr]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
