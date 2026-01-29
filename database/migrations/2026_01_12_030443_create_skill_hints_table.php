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
        Schema::create('ucp_skill_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('ucp_skills')->onDelete('cascade');

            // Hint source information
            $table->enum('source_type', ['support_card', 'event', 'inheritance', 'training', 'race'])->comment('Source of the skill hint');
            $table->string('source_name')->comment('Name of the specific source (card name, event name, etc.)');
            $table->foreignId('source_id')->nullable()->comment('ID of the source entity if applicable');

            // Hint acquisition details
            $table->integer('turn_obtained')->comment('Turn number when hint was obtained');
            $table->enum('career_phase', ['junior', 'classic', 'senior'])->comment('Career phase when obtained');
            $table->boolean('guaranteed_hint')->default(false)->comment('Whether this was a guaranteed red "!" hint');

            // Training context (for training-based hints)
            $table->enum('training_type', ['speed', 'stamina', 'power', 'guts', 'wit', 'rest', 'infirmary', 'recreation'])->nullable()->comment('Training type that provided the hint');
            $table->json('training_participants')->nullable()->comment('Support cards that participated in training');
            $table->boolean('friendship_training')->default(false)->comment('Whether this was friendship training');

            // Hint value and usage
            $table->decimal('discount_percentage', 5, 2)->default(10.00)->comment('SP cost discount percentage (progressive: 10%/20%/30%/35%/40% max at 5 hints)');
            $table->boolean('is_used')->default(false)->comment('Whether hint has been used for skill acquisition');
            $table->timestamp('used_at')->nullable()->comment('When the hint was used');

            // Metadata
            $table->json('hint_metadata')->nullable()->comment('Additional hint information and context');
            $table->timestamps();

            // Indexes for performance
            $table->index(['character_id', 'skill_id']);
            $table->index(['character_id', 'is_used']);
            $table->index('source_type');
            $table->index('turn_obtained');
            $table->index('guaranteed_hint');

            // Unique constraint to prevent duplicate hints from same source
            $table->unique(['character_id', 'skill_id', 'source_type', 'source_name'], 'unique_character_skill_hint_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_skill_hints');
    }
};
