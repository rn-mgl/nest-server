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
        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->renameColumn("user_id", "employee_id");
            $table->dropColumn("score");
        });

        Schema::create('employee_training_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "employee_id")->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(TrainingReview::class, "training_review_id")->constrained()->cascadeOnDelete();
            $table->string("status");
            $table->integer("score")->nullable();
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_training_reviews');
        Schema::table("employee_trainings", function(Blueprint $table) {
            $table->renameColumn("employee_id", "user_id");
            $table->integer("score")->after("status");
        });
    }
};
