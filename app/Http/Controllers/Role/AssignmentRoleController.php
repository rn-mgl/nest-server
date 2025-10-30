<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class AssignmentRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function assignmentIndex(Request $request)
    {
        try {

            $attributes = $request->validate([
                "role_id" => ["required", "integer", "exists:roles,id"]
            ]);

            $users = User::with(
                [
                    "roles" => function ($query) use ($attributes) {
                        $query->where("id", "=", $attributes["role_id"]);
                    },
                    "image"
                ]
            )->get()->each(function ($user) {
                if ($user->relationLoaded("roles")) {
                    $user->role = $user->roles->first();
                    $user->unsetRelation("roles");
                }
            });

            return response()->json(["users" => $users]);

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
    public function assignmentStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "role_id" => ["required", "integer", "exists:roles,id"],
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"]
            ]);

            $role = Role::find($attributes["role_id"]);

            $role->users()->sync($attributes["user_ids"]);

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
