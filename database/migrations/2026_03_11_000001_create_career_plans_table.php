<?php

declare(strict_types=1);

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
        Schema::create('career_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('ucp_users')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('ucp_characters')->cascadeOnDelete();
            $table->text('goal')->nullable();
            $table->json('plan');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedInteger('current_turn')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'character_id']);
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_plans');
    }
};
