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
            $table->fullText("first_name");
            $table->fullText("last_name");
            $table->fullText("email");
            $table->index("created_at");
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->fullText("first_name");
            $table->fullText("last_name");
            $table->fullText("email");
            $table->index("created_at");
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->fullText("type");
            $table->fullText("description");
            $table->index("created_at");
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->fullText("job_title");
            $table->fullText("department");
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->fullText("title");
            $table->fullText("description");
            $table->index("created_at");
        });

        Schema::table('onboardings', function (Blueprint $table) {
            $table->fullText("title");
            $table->fullText("description");
            $table->index("created_at");
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->fullText("title");
            $table->fullText("description");
            $table->index("created_at");
            $table->index("deadline");
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->fullText("name");
            $table->fullText("description");
            $table->index("created_at");
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->fullText("department");
            $table->index("created_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex("first_name");
            $table->dropIndex("last_name");
            $table->dropIndex("email");
            $table->dropIndex("created_at");
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex("first_name");
            $table->dropIndex("last_name");
            $table->dropIndex("email");
            $table->dropIndex("created_at");
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropIndex("type");
            $table->dropIndex("description");
            $table->dropIndex("created_at");
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex("job_title");
            $table->dropIndex("department");
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropIndex("title");
            $table->dropIndex("description");
            $table->dropIndex("created_at");
        });

        Schema::table('onboardings', function (Blueprint $table) {
            $table->dropIndex("title");
            $table->dropIndex("description");
            $table->dropIndex("created_at");
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropIndex("title");
            $table->dropIndex("description");
            $table->dropIndex("created_at");
            $table->dropIndex("deadline");
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex("name");
            $table->dropIndex("description");
            $table->dropIndex("created_at");
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex("department");
            $table->dropIndex("created_at");
        });
    }
};
