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
        Schema::table('employee_performance_reviews', function (Blueprint $table) {
            $table->string("status")->default("Pending")->after("assigned_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_performance_reviews', function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
};
