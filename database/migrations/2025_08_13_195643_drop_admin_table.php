<?php

use App\Models\Admin;
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
        Schema::table("entity_approvals", function (Blueprint $table) {
            $table->dropForeign(["approved_by"]);

            $table->foreign("approved_by")->references("id")->on("users")->nullOnDelete();
        });

        Schema::table("companies", function (Blueprint $table) {
            $table->dropForeign(["added_by"]);

            $table->foreign("added_by")->references("id")->on("users")->nullOnDelete();
        });

        Schema::drop("admins");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });

        Schema::table("entity_approvals", function (Blueprint $table) {
            $table->dropForeign(["approved_by"]);

            $table->foreign("approved_by")->references("id")->on("users")->nullOnDelete();
        });

        Schema::table("companies", function (Blueprint $table) {
            $table->dropForeign(["added_by"]);

            $table->foreign("added_by")->references("id")->on("users")->nullOnDelete();
        });
    }
};
