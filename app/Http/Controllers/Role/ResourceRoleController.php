<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $roles = Role::with(["createdBy", "permissions"])->get();

            return response()->json(["roles" => $roles]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $attributes = $request->validate([
                "role" => ["string", "required"]
            ]);

            $attributes["created_by"] = Auth::id();

            $created = Role::create($attributes);

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        try {

            return response()->json(["role" => $role->load(["createdBy", "permissions"])]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        try {

            $attributes = $request->validate([
                "role" => ["required", "string"]
            ]);

            $updated = $role->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try {

            return response()->json(["success" => $role->delete()]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
