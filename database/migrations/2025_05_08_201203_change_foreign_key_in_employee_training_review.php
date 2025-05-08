<?php

use App\Models\Training;
use App\Models\TrainingReview;
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
        Schema::table('employee_training_reviews', function (Blueprint $table) {
            $table->dropForeign("employee_training_reviews_training_review_id_foreign");
            $table->dropColumn("training_review_id");
            $table->foreignIdFor(Training::class, "training_id")->after("employee_id")->constrained("trainings")->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_training_reviews', function (Blueprint $table) {
            $table->dropForeign("employee_training_reviews_training_id_foreign");
            $table->dropColumn("training_id");
            $table->foreignIdFor(TrainingReview::class, "training_review_id")->after("employee_id")->constrained("training_reviews")->cascadeOnDelete();
        });
    }
};
