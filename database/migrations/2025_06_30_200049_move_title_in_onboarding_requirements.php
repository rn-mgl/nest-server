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
        Schema::table('onboarding_policy_acknowledgements', function (Blueprint $table) {
            $table->string("title")->after("onboarding_id")->change();
        });

        Schema::table('onboarding_required_documents', function (Blueprint $table) {
            $table->string("title")->after("onboarding_id")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_policy_acknowledgements', function (Blueprint $table) {
            $table->string("title")->after("id")->change();
        });

        Schema::table('onboarding_required_documents', function (Blueprint $table) {
            $table->string("title")->after("id")->change();
        });
    }
};
