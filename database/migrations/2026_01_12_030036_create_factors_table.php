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
        Schema::create('ucp_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Factor classification
            $table->enum('factor_type', ['blue_stats', 'red_aptitudes', 'green_unique_skills', 'white_normal_skills'])->comment('Factor category type');
            $table->string('factor_name')->comment('Name of the specific factor');
            $table->enum('star_level', ['1_star', '2_star', '3_star'])->comment('Factor star level (★☆☆, ★★☆, ★★★)');

            // Blue stat factors (★☆☆ = +5, ★★☆ = +12, ★★★ = +21)
            $table->enum('stat_type', ['speed', 'stamina', 'power', 'guts', 'wit'])->nullable()->comment('Stat type for blue factors');
            $table->integer('stat_bonus')->nullable()->comment('Stat bonus value based on star level');

            // Red aptitude factors (1★ = 1 grade up, then 3★ per additional grade)
            $table->enum('aptitude_type', ['sprint', 'mile', 'medium', 'long', 'turf', 'dirt', 'front_runner', 'pace_chaser', 'late_surger', 'end_closer'])->nullable()->comment('Aptitude type for red factors');
            $table->integer('grade_improvement')->nullable()->comment('Number of aptitude grades improved');

            // Green unique skill factors (guaranteed from 3★ characters)
            $table->string('unique_skill_name')->nullable()->comment('Name of unique skill for green factors');
            $table->json('skill_effects')->nullable()->comment('Unique skill effects and bonuses');

            // White normal skill/race bonus factors
            $table->string('normal_skill_name')->nullable()->comment('Name of normal skill for white factors');
            $table->json('race_bonuses')->nullable()->comment('Race-specific bonuses and conditions');

            // Inheritance tracking
            $table->enum('source_parent', ['main_parent_1', 'main_parent_2', 'grandparent_1', 'grandparent_2', 'grandparent_3', 'grandparent_4'])->comment('Which parent provided this factor');
            $table->string('source_character_name')->nullable()->comment('Name of the source character');
            $table->decimal('inheritance_rate', 5, 2)->default(100.00)->comment('Success rate percentage for inheritance');
            $table->boolean('affinity_compatible')->default(false)->comment('Whether ◎ affinity symbol applies');

            // Status and metadata
            $table->boolean('is_active')->default(true)->comment('Whether factor is currently active');
            $table->json('factor_metadata')->nullable()->comment('Additional factor information');
            $table->timestamps();

            // Indexes for performance
            $table->index(['character_id', 'factor_type']);
            $table->index(['character_id', 'is_active']);
            $table->index('source_parent');
            $table->index('star_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_factors');
    }
};
