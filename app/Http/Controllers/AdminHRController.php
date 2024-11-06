<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

class AdminHRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $attributes = $request->validate([
            "verified" => ["required"]
        ]);

        $attributes["verified"] = filter_var($attributes["verified"], FILTER_VALIDATE_BOOLEAN);

        $hrs = DB::table("users")
                ->where("role", "=", "hr")
                ->when($attributes["verified"], function($query) {
                    return $query->whereNotNull("email_verified_at");
                })
                ->when(!$attributes["verified"], function($query) {
                    return $query->whereNull("email_verified_at");
                })
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
