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
        Schema::create('ucp_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Skill name (e.g., "Go with the Flow", "Lane Legerdemain")');
            $table->string('name_en')->nullable()->comment('English name of the skill'); // Consolidated from 2026_01_25_103328
            $table->string('internal_id')->unique()->comment('Internal game ID for skill');

            // Skill classification
            $table->enum('skill_type', ['speed', 'passive', 'recovery', 'debuff', 'unique'])->comment('Skill category type');
            $table->enum('rarity', ['normal', 'rare', 'unique'])->comment('Skill rarity level');
            $table->integer('base_sp_cost')->comment('Base SP cost (Normal: 120-180, Rare: 180-240, Unique: variable)');

            // Skill evolution system
            $table->foreignId('evolution_target_id')->nullable()->constrained('ucp_skills')->comment('Target skill for Normal → Rare evolution');
            $table->foreignId('evolution_source_id')->nullable()->constrained('ucp_skills')->comment('Source skill that evolves into this one');
            $table->boolean('can_evolve')->default(false)->comment('Whether this skill can evolve to a rare version');
            $table->boolean('is_evolution')->default(false)->comment('Whether this skill is an evolved version');

            // Skill effects and metadata
            $table->json('effects')->comment('Skill effects, bonuses, and activation conditions');
            $table->text('description')->comment('Skill description and usage notes');
            $table->json('activation_conditions')->nullable()->comment('Conditions for skill activation during races');
            $table->json('stat_requirements')->nullable()->comment('Stat requirements for optimal skill effectiveness');

            // Skill provision and acquisition
            $table->json('support_card_sources')->nullable()->comment('Support cards that can provide hints for this skill');
            $table->json('event_sources')->nullable()->comment('Events that can provide this skill or hints');
            $table->json('inheritance_sources')->nullable()->comment('Characters that can inherit this skill');

            // Meta and strategic information
            $table->enum('meta_tier', ['S+', 'S', 'A', 'B', 'C'])->nullable()->comment('Current meta tier ranking');
            $table->json('strategic_notes')->nullable()->comment('Strategic usage notes and recommendations');
            $table->json('synergy_skills')->nullable()->comment('Skills that synergize well with this skill');

            // Status and metadata
            $table->boolean('is_active')->default(true)->comment('Whether skill is currently available in game');
            $table->string('status')->default('active')->comment('Operational status for skill availability'); // Consolidated from 2026_01_23_010000
            $table->timestamps();

            // Indexes for performance
            $table->index('skill_type');
            $table->index('rarity');
            $table->index(['rarity', 'skill_type']);
            $table->index('base_sp_cost');
            $table->index('can_evolve');
            $table->index('is_evolution');
            $table->index('meta_tier');
            $table->index('status'); // Consolidated from 2026_01_23_010000
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_skills');
    }
};
