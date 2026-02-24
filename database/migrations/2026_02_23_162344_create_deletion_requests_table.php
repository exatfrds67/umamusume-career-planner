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
        Schema::create('ucp_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('ucp_users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->timestamp('grace_period_ends_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('deletion_log')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('grace_period_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_deletion_requests');
    }
};
