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
        Schema::create('user_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id')->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'onboarded_by')->nullable()->constrained("users")->nullOnDelete();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default("pending");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean("is_deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_onboardings');
    }
};
