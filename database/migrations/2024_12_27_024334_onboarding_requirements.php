<?php

use App\Models\Onboarding;
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
        Schema::create('onboarding_required_documents', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "created_by")->nullable()->constrained()->nullOnDelete();
            $table->string("title");
            $table->longText("description");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes("deleted_at", 0);
        });

        Schema::create('onboarding_policy_acknowledgements', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "created_by")->nullable()->constrained()->nullOnDelete();
            $table->string("title");
            $table->longText("description");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes("deleted_at", 0);
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
