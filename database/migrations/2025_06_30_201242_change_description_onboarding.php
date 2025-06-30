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
        Schema::table("onboarding_policy_acknowledgements", function (Blueprint $table) {
            $table->dropColumn("policy");
            $table->longText("description")->after("title");
        });

        Schema::table("onboarding_required_documents", function (Blueprint $table) {
            $table->dropColumn("document");
            $table->longText("description")->after("title");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("onboarding_policy_acknowledgements", function (Blueprint $table) {
            $table->dropColumn("description");
            $table->string("policy")->after("title");
        });

        Schema::table("onboarding_required_documents", function (Blueprint $table) {
            $table->dropColumn("description");
            $table->string("document")->after("title");
        });
    }
};
