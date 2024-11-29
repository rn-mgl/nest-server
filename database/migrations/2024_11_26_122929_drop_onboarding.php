<?php

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
        Schema::dropIfExists('onboardings');

        Schema::create('onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("title");
            $table->longText("description");
            $table->longText("required_documents");
            $table->longText("policy_acknowledgements");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboardings');
    }
};
