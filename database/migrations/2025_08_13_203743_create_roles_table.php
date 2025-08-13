<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string("role")->unique()->fulltext();
            $table->foreignIdFor(User::class, "created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });

        Role::insert([
            ["role" => "employee"],
            ["role" => "hr"],
            ["role" => "admin"],
            ["role" => "super_admin"]
        ]);

        Schema::table("users", function(Blueprint $table) {
            $table->unsignedBigInteger( "role_id")->nullable()->after("image");
        });

        $roles = Role::all()->keyBy("role");

        User::all()->each(function ($user) use ($roles) {
            if (isset($roles[$user->role])) {
                $user->role_id = $roles[$user->role]->id;
                $user->save();
            }
        });

        Schema::table("users", function(Blueprint $table) {
            $table->dropColumn("role");
            $table->foreign("role_id")->references("id")->on("roles")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }
};
