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
        Schema::table("training_contents", function(Blueprint $table) {
            $table->string("title")->after("training_id");
            $table->longText("description")->after("title");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("training_contents", function(Blueprint $table) {
            $table->dropColumn("title");
            $table->dropColumn("description");
        });
    }
};
