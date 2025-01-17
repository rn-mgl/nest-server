<?php

use App\Models\PerformanceReview;
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
        Schema::create('employee_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PerformanceReview::class, "performance_review_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "assigned_to")->constrained("users")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_performance_reviews');
    }
};
