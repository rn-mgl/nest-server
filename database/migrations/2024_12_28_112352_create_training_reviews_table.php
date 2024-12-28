<?php

use App\Models\Training;
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
        Schema::create('training_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Training::class, "training_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->longText("question");
            $table->text("answer");
            $table->text("choice_1");
            $table->text("choice_2");
            $table->text("choice_3");
            $table->text("choice_4");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_reviews');
    }
};
