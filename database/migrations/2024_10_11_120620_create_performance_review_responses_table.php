<?php

use App\Models\PerformanceReviewContent;
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
        Schema::create('user_performance_review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PerformanceReviewContent::class, "content_id")->constrained("performance_review_contents")->cascadeOnDelete();
            $table->foreignIdFor(User::class, "user_id")->constrained("users")->cascadeOnDelete();
            $table->longText("response");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes("deleted_at", 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_performance_review_responses');
    }
};
