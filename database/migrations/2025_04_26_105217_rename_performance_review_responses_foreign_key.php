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
        Schema::table("employee_performance_review_responses", function(Blueprint $table) {
            $table->renameColumn("review_content_id", "performance_review_content_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("employee_performance_review_responses", function(Blueprint $table) {
            $table->renameColumn("performance_review_content_id", "review_content_id");
        });
    }
};
