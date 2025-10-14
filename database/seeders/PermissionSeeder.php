<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // permissions
        $permissions = [
            [
                "name" => "Create Permission",
                "description" => "This allows users to create a permission.",
                "action" => "create.permission",
                "created_by" => 1
            ],
            [
                "name" => "Read Permission",
                "description" => "This allows users to read a permission.",
                "action" => "read.permission",
                "created_by" => 1
            ],
            [
                "name" => "Update Permission",
                "description" => "This allows users to update a permission.",
                "action" => "update.permission",
                "created_by" => 1
            ],
            [
                "name" => "Delete Permission",
                "description" => "This allows users to delete a permission.",
                "action" => "delete.permission",
                "created_by" => 1
            ],
            [
                "name" => "Create Role",
                "description" => "This allows users to create a role.",
                "action" => "create.role",
                "created_by" => 1
            ],
            [
                "name" => "Read Role",
                "description" => "This allows users to read a role.",
                "action" => "read.role",
                "created_by" => 1
            ],
            [
                "name" => "Update Role",
                "description" => "This allows users to update a role.",
                "action" => "update.role",
                "created_by" => 1
            ],
            [
                "name" => "Delete Role",
                "description" => "This allows users to delete a role.",
                "action" => "delete.role",
                "created_by" => 1
            ],
            [
                "name" => "Create Permission to Role",
                "description" => "This allows users to create a permission to role connection.",
                "action" => "create.permission_role",
                "created_by" => 1
            ],
            [
                "name" => "Read Permission to Role",
                "description" => "This allows users to read a permission to role connection.",
                "action" => "read.permission_role",
                "created_by" => 1
            ],
            [
                "name" => "Update Permission to Role",
                "description" => "This allows users to update a permission to role connection.",
                "action" => "update.permission_role",
                "created_by" => 1
            ],
            [
                "name" => "Delete Permission to Role",
                "description" => "This allows users to delete a permission to role connection.",
                "action" => "delete.permission_role",
                "created_by" => 1
            ],
            [
                "name" => "Create Role to User",
                "description" => "This allows users to create a role to user connection.",
                "action" => "create.role_user",
                "created_by" => 1
            ],
            [
                "name" => "Read Role to User",
                "description" => "This allows users to read a role to user connection.",
                "action" => "read.role_user",
                "created_by" => 1
            ],
            [
                "name" => "Update Role to User",
                "description" => "This allows users to update a role to user connection.",
                "action" => "update.role_user",
                "created_by" => 1
            ],
            [
                "name" => "Delete Role to User",
                "description" => "This allows users to delete a role to user connection.",
                "action" => "delete.role_user",
                "created_by" => 1
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }

        $superAdmin = Role::firstOrCreate(["role" => "super_admin", "created_by" => 1]);
        $admin = Role::firstOrCreate(["role" => "admin", "created_by" => 1]);
        $hr = Role::firstOrCreate(["role" => "hr", "created_by" => 1]);
        $employee = Role::firstOrCreate(["role" => "employee", "created_by" => 1]);

        $superAdmin->permissions()->sync(Permission::pluck("id")->toArray());
    }
}
