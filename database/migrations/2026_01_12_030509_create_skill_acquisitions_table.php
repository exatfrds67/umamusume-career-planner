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
        Schema::create('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('ucp_skills')->onDelete('cascade');
            $table->unsignedBigInteger('career_id')->nullable()->comment('Career run when skill was acquired (FK to be added later)');

            // Acquisition details
            $table->integer('turn_acquired')->comment('Turn number when skill was acquired');
            $table->enum('career_phase', ['junior', 'classic', 'senior'])->comment('Career phase when acquired');
            $table->enum('acquisition_method', ['purchase', 'evolution', 'inheritance', 'event', 'race_reward'])->comment('How the skill was acquired');

            // Cost and hint information
            $table->integer('base_sp_cost')->comment('Base SP cost of the skill');
            $table->integer('hints_used')->default(0)->comment('Number of hints used for discount');
            $table->decimal('total_discount_percentage', 5, 2)->default(0.00)->comment('Total discount percentage applied');
            $table->integer('final_sp_cost')->comment('Final SP cost paid after discounts');
            $table->integer('sp_saved')->default(0)->comment('SP saved through hint discounts');

            // Evolution tracking
            $table->boolean('is_evolution')->default(false)->comment('Whether this was acquired through skill evolution');
            $table->foreignId('evolved_from_skill_id')->nullable()->constrained('ucp_skills')->comment('Original skill that evolved into this one');
            $table->boolean('replaced_skill')->default(false)->comment('Whether this skill replaced another skill');

            // Strategic context
            $table->json('acquisition_context')->nullable()->comment('Context and reasoning for skill acquisition');
            $table->json('hint_sources')->nullable()->comment('Sources of hints used for this acquisition');
            $table->enum('priority_level', ['high', 'medium', 'low'])->default('medium')->comment('Strategic priority of this skill acquisition');

            // Performance tracking
            $table->integer('races_used')->default(0)->comment('Number of races where skill was used');
            $table->json('performance_data')->nullable()->comment('Skill performance statistics');
            $table->decimal('effectiveness_rating', 3, 2)->nullable()->comment('Skill effectiveness rating 0.00-10.00');

            // Status and metadata
            $table->boolean('is_active')->default(true)->comment('Whether skill is currently active on character');
            $table->json('acquisition_metadata')->nullable()->comment('Additional acquisition information');
            $table->timestamps();

            // Indexes for performance
            $table->index(['character_id', 'skill_id']);
            $table->index(['character_id', 'is_active']);
            $table->index('turn_acquired');
            $table->index('acquisition_method');
            $table->index('is_evolution');
            $table->index('priority_level');

            // Unique constraint to prevent duplicate skill acquisitions
            $table->unique(['character_id', 'skill_id'], 'unique_character_skill_acquisition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_skill_acquisitions');
    }
};
