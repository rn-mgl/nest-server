<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminHRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {

            // Query the 'users' table, applying search and sorting filters
            $hrs = User::ofRole("hr")
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
    public function update(Request $request, User $hr)
    {
        try {

            $attributes = $request->validate([
                "type" => ["required", "string"]
            ]);

            switch ($attributes["type"]) {
                case "deactivate":
                    $updated = $hr->update(["email_verified_at" => null]);
                    return response()->json(["success" => $updated]);
                case "verify":
                    $updated = $hr->update(["email_verified_at" => Carbon::now()]);
                    return response()->json(["success" => $updated]);
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
}
