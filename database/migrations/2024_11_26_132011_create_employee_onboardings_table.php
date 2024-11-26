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
        Schema::create('employee_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'employee_id')->constrained("users")->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'onboarded_by')->nullable()->constrained("users")->nullOnDelete();
            $table->foreignIdFor(Onboarding::class, 'onboarding_id')->constrained()->cascadeOnDelete();
            $table->boolean('completed_documents')->default(false);
            $table->boolean('policy_acknowledged')->default(false);
            $table->string('status')->default("Pending");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_onboardings');
    }
};
