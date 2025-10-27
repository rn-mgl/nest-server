<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourcePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function resourceIndex()
    {
        try {

            $permissions = Permission::with(["createdBy"])->get();

            return response()->json(["permissions" => $permissions]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "name" => ["required", "string"],
                "action" => ["required", "string"],
                "description" => ["required", "string"]
            ]);

            $attributes["created_by"] = Auth::id();

            $created = Permission::create($attributes);

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function resourceShow(Permission $permission)
    {
        try {

            return response()->json(["permission" => $permission]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, Permission $permission)
    {
        try {

            $attributes = $request->validate([
                "name" => ["required", "string"],
                "action" => ["required", "string"],
                "description" => ["required", "string"]
            ]);

            $updated = $permission->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function resourceDestroy(Permission $permission)
    {
        try {

            return response()->json(["success" => $permission->delete()]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
