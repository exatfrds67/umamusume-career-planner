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
        Schema::create('ucp_training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->onDelete('cascade');
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Training session identification
            $table->integer('turn_number')->comment('Turn number when training occurred');
            $table->enum('career_phase', ['junior', 'classic', 'senior'])->comment('Career phase when training occurred');
            $table->enum('training_type', ['speed', 'stamina', 'power', 'guts', 'wit', 'rest', 'infirmary', 'recreation'])->comment('Type of training performed');

            // Training participants and context
            $table->json('support_cards_present')->nullable()->comment('Support cards present during training');
            $table->json('participating_support_cards')->nullable()->comment('Support cards that participated in training');
            $table->boolean('friendship_training')->default(false)->comment('Whether this was friendship training');
            $table->integer('friendship_level_bonus')->default(0)->comment('Friendship level bonus applied');

            // Training conditions
            $table->enum('character_condition', ['perfect', 'good', 'normal', 'bad', 'very_bad'])->default('normal')->comment('Character condition during training');
            $table->enum('motivation', ['very_high', 'high', 'normal', 'low', 'very_low'])->default('normal')->comment('Character motivation level');
            $table->boolean('had_failure_rate')->default(false)->comment('Whether training had failure rate');
            $table->decimal('failure_rate_percentage', 5, 2)->nullable()->comment('Failure rate percentage if applicable');

            // Training results - stat gains
            $table->integer('speed_gain')->default(0)->comment('Speed stat gained from training');
            $table->integer('stamina_gain')->default(0)->comment('Stamina stat gained from training');
            $table->integer('power_gain')->default(0)->comment('Power stat gained from training');
            $table->integer('guts_gain')->default(0)->comment('Guts stat gained from training');
            $table->integer('wit_gain')->default(0)->comment('Wit stat gained from training');
            $table->integer('sp_gain')->default(0)->comment('Skill points gained from training');

            // Training results - additional outcomes
            $table->json('skill_hints_obtained')->nullable()->comment('Skill hints obtained during training');
            $table->json('events_triggered')->nullable()->comment('Events triggered during training');
            $table->boolean('training_failed')->default(false)->comment('Whether training failed');
            $table->text('failure_reason')->nullable()->comment('Reason for training failure if applicable');

            // Energy and health impact
            $table->integer('energy_cost')->comment('Energy consumed by training');
            $table->integer('energy_before')->comment('Energy level before training');
            $table->integer('energy_after')->comment('Energy level after training');
            $table->boolean('injury_occurred')->default(false)->comment('Whether injury occurred during training');
            $table->string('injury_type')->nullable()->comment('Type of injury if occurred');

            // Training efficiency and analysis
            $table->decimal('training_efficiency', 5, 2)->nullable()->comment('Training efficiency rating');
            $table->integer('total_stat_points_gained')->comment('Total stat points gained from this training');
            $table->json('training_bonuses')->nullable()->comment('Bonuses applied during training');
            $table->json('training_penalties')->nullable()->comment('Penalties applied during training');

            // Strategic context
            $table->text('training_notes')->nullable()->comment('User notes about this training session');
            $table->enum('strategic_priority', ['high', 'medium', 'low'])->default('medium')->comment('Strategic priority of this training');
            $table->json('decision_factors')->nullable()->comment('Factors that influenced training choice');

            // Metadata
            $table->json('training_metadata')->nullable()->comment('Additional training session information');
            $table->timestamps();

            // Indexes for performance
            $table->index(['career_id', 'turn_number']);
            $table->index(['character_id', 'training_type']);
            $table->index('training_type');
            $table->index('career_phase');
            $table->index('friendship_training');
            $table->index('training_failed');
            $table->index('injury_occurred');
            $table->index('total_stat_points_gained');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_training_sessions');
    }
};
