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
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('entity_approvals', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('user_performance_reviews', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('performance_review_contents', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });
        Schema::table('performance_review_responses', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('onboardings', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('training_contents', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('user_trainings', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->boolean("is_deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('entity_approvals', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('user_performance_reviews', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('performance_review_contents', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });
        Schema::table('performance_review_responses', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('onboardings', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('training_contents', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('user_trainings', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn("is_deleted");
        });
    }
};
