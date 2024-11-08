<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {

        $attributes = $request->validate([
            "categoryKey" => ["required"],
            "categoryValue" => ["required"],
            "sortKey" => ["required"],
            "isAsc" => ["required"],
        ]);

        $attributes["verified"] = filter_var($attributes["categoryValue"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $attributes["isAsc"] = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $sortType = $attributes["isAsc"] === true ? "ASC" : "DESC";

        $hrs = DB::table("users")
                ->where("role", "=", "hr")
                ->when($attributes["verified"] === true, function($query) {
                    return $query->whereNotNull("email_verified_at");
                })
                ->when($attributes["verified"] === false, function($query) {
                    return $query->whereNull("email_verified_at");
                })
                ->orderBy($attributes["sortKey"], $sortType)
                ->get();

        return response()->json(["hrs" =>  $hrs]);
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
