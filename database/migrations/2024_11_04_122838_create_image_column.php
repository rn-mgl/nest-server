<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string("image")->after("role")->nullable();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string("image")->after("password")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("image");
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn("image");
        });
    }
};
