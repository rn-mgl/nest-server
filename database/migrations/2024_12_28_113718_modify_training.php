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
        Schema::table("trainings", function(Blueprint $table) {
            $table->dropColumn("deadline");
            $table->integer("deadline_days")->nullable()->after("description");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("trainings", function(Blueprint $table) {
            $table->timestamp("deadline")->nullable();
            $table->dropColumn("deadline_days");
        });
    }
};