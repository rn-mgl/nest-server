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
        Schema::create('user_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PerformanceReview::class, "performance_review_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(User::class, "assigned_by")->nullable()->constrained("users")->nullOnDelete();
            $table->string("status")->default("pending");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            $table->boolean("is_deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_performance_reviews');
    }
};
