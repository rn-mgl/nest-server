<?php

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
        Schema::table("employee_onboardings", function(Blueprint $table) {
            $table->renameColumn("onboarded_by", "assigned_by");
        });

        Schema::table("employee_performance_reviews", function(Blueprint $table) {
            $table->foreignIdFor(User::class, "assigned_by")->nullable()->after("employee_id")->constrained("users")->nullOnDelete();
        });

        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->foreignIdFor(User::class, "assigned_by")->nullable()->after("employee_id")->constrained("users")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("employee_onboardings", function(Blueprint $table) {
            $table->renameColumn("assigned_by", "onboarded_by");
        });

        Schema::table("employee_performance_reviews", function(Blueprint $table) {
            $table->dropColumn("assigned_by");
        });

        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->dropColumn("assigned_by");
        });
    }
};
