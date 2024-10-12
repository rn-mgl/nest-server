<?php

use App\Models\Training;
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
        Schema::create('training_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Training::class, "training_id")->constrained()->cascadeOnDelete();
            $table->string("question");
            $table->string("answer");
            $table->string("choice_1");
            $table->string("choice_2");
            $table->string("choice_3");
            $table->string("choice_4");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_contents');
    }
};
