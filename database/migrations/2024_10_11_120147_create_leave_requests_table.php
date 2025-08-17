<?php

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "user_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, "approved_by")->nullable()->constrained("users")->nullOnDelete();
            $table->foreignIdFor(LeaveType::class, "leave_type_id")->constrained()->cascadeOnDelete();
            $table->timestamp("start_date")->nullable();
            $table->timestamp("end_date")->nullable();
            $table->string("status")->default("pending");
            $table->longText("reason");
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
        Schema::dropIfExists('leave_requests');
    }
};
