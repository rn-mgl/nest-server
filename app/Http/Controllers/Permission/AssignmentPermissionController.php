<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentPermissionController extends Controller
{

    public function assignmentIndex(Request $request)
    {
        try {

            $attributes = $request->validate([
                "permission_id" => ["required", "integer", "exists:permissions,id"]
            ]);

            $roles = Role::with([
                "permissions" => function ($query) use ($attributes) {
                    $query->where("id", "=", $attributes["permission_id"])->withTrashed();
                }
            ])->get()->each(function ($role) {

                if ($role->relationLoaded("permissions")) {
                    $role->permission = $role->permissions->first();
                    $role->unsetRelation("permissions");
                }

            });

            return response()->json(["roles" => $roles]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function assignmentStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "permission_id" => ["required", "integer", "exists:permissions,id"],
                "role_ids" => ["array"],
                "role_ids.*" => ["integer", "exists:roles,id"]
            ]);

            DB::transaction(function () use ($attributes) {
                $checkedRoleIds = collect($attributes["role_ids"]);

                // get the roles of the target permission
                $permissionRoles = Permission::with(["roles"])->where("id", "=", $attributes["permission_id"])->first();

                // attach and detach roles
                $permissionRoles->roles()->sync($checkedRoleIds);
            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

}
