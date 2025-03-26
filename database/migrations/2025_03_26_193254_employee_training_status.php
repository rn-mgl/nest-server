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
        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->string("status")->default("Pending")->change();
        });

        Schema::table("employee_training_reviews", function(Blueprint $table) {
            $table->string("status")->default("Pending")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->string("status")->default(null)->change();
        });

        Schema::table("employee_training_reviews", function(Blueprint $table) {
            $table->string("status")->default(null)->change();
        });

    }
};
