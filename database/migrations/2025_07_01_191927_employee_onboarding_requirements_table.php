<?php

use App\Models\OnboardingPolicyAcknowledgements;
use App\Models\OnboardingRequiredDocuments;
use App\Models\User;
use Cloudinary\Transformation\Scale;
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
        Schema::create("user_onboarding_policy_acknowledgements", function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(OnboardingPolicyAcknowledgements::class, "policy_acknowledgement_id")->constrained("onboarding_policy_acknowledgements", "id", "policy_acknowledgement_id_foreign")->cascadeOnDelete();
            $table->boolean("acknowledged")->default(false);
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            $table->boolean("is_deleted")->default(false);
        });

        Schema::create("user_onboarding_required_document", function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(OnboardingRequiredDocuments::class, "required_document_id")->constrained("onboarding_required_documents", "id", "required_document_id_foreign")->cascadeOnDelete();
            $table->string("document")->nullable();
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            $table->boolean("is_deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("user_onboarding_policy_acknowledgement");
        Schema::dropIfExists("user_onboarding_required_document");
    }
};
