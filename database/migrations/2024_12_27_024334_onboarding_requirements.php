<?php

use App\Models\Onboarding;
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
        Schema::create('onboarding_required_documents', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->string('document');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('onboarding_policy_acknowledgements', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->string('policy');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::table('onboardings', function(Blueprint $table) {
            $table->dropColumn('required_documents');
            $table->dropColumn('policy_acknowledgements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_required_documents');
        Schema::dropIfExists('onboarding_policy_acknowledgements');
    }
};
