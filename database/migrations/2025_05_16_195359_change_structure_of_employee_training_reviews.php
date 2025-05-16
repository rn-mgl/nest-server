<?php

use App\Models\TrainingReview;
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
        Schema::drop("employee_training_reviews");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('employee_training_review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "response_by")->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(TrainingReview::class, "training_review_id")->constrained("training_reviews")->cascadeOnDelete();
            $table->string("answer");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }
};
