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
        Schema::create('ucp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->onDelete('cascade');
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Event identification
            $table->string('event_name')->comment('Name of the event');
            $table->string('event_internal_id')->nullable()->comment('Internal game ID for the event');
            $table->integer('turn_number')->comment('Turn number when event occurred');
            $table->enum('career_phase', ['junior', 'classic', 'senior'])->comment('Career phase when event occurred');

            // Event classification
            $table->enum('event_type', ['training', 'random', 'support_card', 'character_specific', 'scenario_specific', 'race_result', 'special'])->comment('Type of event');
            $table->enum('event_category', ['positive', 'negative', 'neutral', 'choice'])->comment('Event outcome category');
            $table->enum('trigger_source', ['training', 'support_card', 'random', 'race', 'turn_start', 'turn_end', 'special'])->comment('What triggered this event');

            // Event source information
            $table->string('source_name')->nullable()->comment('Name of the source (support card, character, etc.)');
            $table->foreignId('source_support_card_id')->nullable()->constrained('ucp_support_cards')->comment('Support card that triggered event');
            $table->string('source_character')->nullable()->comment('Character associated with event');

            // Event choices and outcomes
            $table->json('available_choices')->nullable()->comment('Choices presented to player');
            $table->string('chosen_option')->nullable()->comment('Option chosen by player');
            $table->json('choice_effects')->nullable()->comment('Effects of the chosen option');
            $table->text('event_description')->nullable()->comment('Event description text');

            // Event effects - stat changes
            $table->integer('speed_change')->default(0)->comment('Speed stat change from event');
            $table->integer('stamina_change')->default(0)->comment('Stamina stat change from event');
            $table->integer('power_change')->default(0)->comment('Power stat change from event');
            $table->integer('guts_change')->default(0)->comment('Guts stat change from event');
            $table->integer('wit_change')->default(0)->comment('Wit stat change from event');
            $table->integer('sp_change')->default(0)->comment('Skill points change from event');

            // Event effects - condition changes
            $table->integer('energy_change')->default(0)->comment('Energy change from event');
            $table->integer('motivation_change')->default(0)->comment('Motivation change from event');
            $table->integer('health_change')->default(0)->comment('Health/condition change from event');
            $table->integer('fans_change')->default(0)->comment('Fan count change from event');

            // Event effects - special outcomes
            $table->json('skill_hints_gained')->nullable()->comment('Skill hints obtained from event');
            $table->json('skills_learned')->nullable()->comment('Skills directly learned from event');
            $table->json('items_gained')->nullable()->comment('Items obtained from event');
            $table->json('special_effects')->nullable()->comment('Special effects applied by event');

            // Event effects - relationship changes
            $table->json('friendship_changes')->nullable()->comment('Support card friendship changes');
            $table->json('bond_changes')->nullable()->comment('Character bond changes');
            $table->json('relationship_effects')->nullable()->comment('Other relationship effects');

            // Event context and conditions
            $table->json('activation_conditions')->nullable()->comment('Conditions that allowed event to trigger');
            $table->json('character_state_before')->nullable()->comment('Character state before event');
            $table->json('character_state_after')->nullable()->comment('Character state after event');
            $table->boolean('was_guaranteed')->default(false)->comment('Whether event was guaranteed to occur');

            // Strategic analysis
            $table->enum('strategic_impact', ['very_positive', 'positive', 'neutral', 'negative', 'very_negative'])->default('neutral')->comment('Strategic impact assessment');
            $table->text('event_notes')->nullable()->comment('User notes about this event');
            $table->json('lessons_learned')->nullable()->comment('Lessons learned from event outcome');

            // Metadata
            $table->json('event_metadata')->nullable()->comment('Additional event information');
            $table->timestamps();

            // Indexes for performance
            $table->index(['career_id', 'turn_number']);
            $table->index(['character_id', 'event_type']);
            $table->index('event_type');
            $table->index('event_category');
            $table->index('trigger_source');
            $table->index('source_support_card_id');
            $table->index('career_phase');
            $table->index('strategic_impact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_events');
    }
};