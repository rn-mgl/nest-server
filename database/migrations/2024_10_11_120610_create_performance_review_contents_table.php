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
        Schema::create('performance_review_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "created_by")->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(PerformanceReview::class, "performance_review_id")->constrained()->cascadeOnDelete();
            $table->longText("survey");
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
        Schema::dropIfExists('performance_review_contents');
    }
};
