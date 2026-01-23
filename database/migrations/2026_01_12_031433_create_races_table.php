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
        Schema::create('ucp_races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->onDelete('cascade');
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Race identification
            $table->string('race_name')->comment('Name of the race');
            $table->string('race_internal_id')->nullable()->comment('Internal game ID for the race');
            $table->integer('turn_number')->comment('Turn number when race occurred');
            $table->enum('career_phase', ['junior', 'classic', 'senior'])->comment('Career phase when race occurred');

            // Race characteristics
            $table->enum('race_grade', ['debut', 'maiden', 'G3', 'G2', 'G1', 'URA1', 'URA2', 'URA3', 'URA_FINAL'])->comment('Race grade/class');
            $table->enum('distance_category', ['short', 'mile', 'intermediate', 'long'])->comment('Race distance category');
            $table->integer('distance_meters')->comment('Exact race distance in meters');
            $table->enum('surface', ['turf', 'dirt'])->comment('Race surface type');
            $table->enum('running_style', ['escape', 'leading', 'insert', 'tracking'])->comment('Running style used in race');

            // Race conditions
            $table->enum('weather', ['sunny', 'rainy', 'cloudy', 'snowy'])->default('sunny')->comment('Weather conditions during race');
            $table->enum('track_condition', ['firm', 'good', 'yielding', 'soft', 'heavy'])->default('good')->comment('Track condition');
            $table->integer('field_size')->comment('Number of horses in the race');
            $table->json('race_conditions')->nullable()->comment('Additional race conditions and requirements');

            // Character state during race
            $table->enum('character_condition', ['perfect', 'good', 'normal', 'bad', 'very_bad'])->default('normal')->comment('Character condition during race');
            $table->enum('motivation', ['very_high', 'high', 'normal', 'low', 'very_low'])->default('normal')->comment('Character motivation level');
            $table->integer('energy_level')->comment('Character energy level before race');

            // Character stats at race time
            $table->integer('speed_at_race')->comment('Speed stat at time of race');
            $table->integer('stamina_at_race')->comment('Stamina stat at time of race');
            $table->integer('power_at_race')->comment('Power stat at time of race');
            $table->integer('guts_at_race')->comment('Guts stat at time of race');
            $table->integer('wit_at_race')->comment('Wit stat at time of race');

            // Race results
            $table->integer('finish_position')->comment('Final finishing position');
            $table->decimal('finish_time', 8, 3)->nullable()->comment('Race finish time in seconds');
            $table->boolean('won_race')->comment('Whether the race was won');
            $table->integer('margin_of_victory')->nullable()->comment('Margin of victory/defeat in lengths');
            $table->enum('race_result', ['victory', 'place', 'show', 'out_of_money'])->comment('Race result category');

            // Race performance metrics
            $table->json('skills_activated')->nullable()->comment('Skills that activated during the race');
            $table->json('race_segments')->nullable()->comment('Performance in different race segments');
            $table->decimal('speed_rating', 5, 2)->nullable()->comment('Speed rating for this race performance');
            $table->json('performance_analysis')->nullable()->comment('Detailed race performance analysis');

            // Rewards and consequences
            $table->integer('fans_gained')->default(0)->comment('Fans gained from race result');
            $table->integer('sp_reward')->default(0)->comment('Skill points rewarded for race');
            $table->json('item_rewards')->nullable()->comment('Items rewarded from race');
            $table->json('stat_bonuses')->nullable()->comment('Stat bonuses gained from race');
            $table->boolean('injury_occurred')->default(false)->comment('Whether injury occurred during race');

            // URA Finale specific fields
            $table->boolean('is_ura_finale_race')->default(false)->comment('Whether this is a URA Finale race');
            $table->enum('ura_finale_stage', ['URA1', 'URA2', 'URA3', 'URA_FINAL'])->nullable()->comment('URA Finale stage if applicable');
            $table->json('ura_finale_requirements')->nullable()->comment('Requirements for URA Finale race');

            // Unity Cup specific fields
            $table->boolean('is_unity_cup_match')->default(false)->comment('Whether this is a Unity Cup match');
            $table->integer('unity_cup_points_earned')->nullable()->comment('Unity Cup points earned from match');
            $table->enum('unity_cup_opponent_rank', ['D', 'C', 'B', 'A', 'S'])->nullable()->comment('Unity Cup opponent rank');

            // Strategic context
            $table->text('race_notes')->nullable()->comment('User notes about this race');
            $table->enum('strategic_importance', ['high', 'medium', 'low'])->default('medium')->comment('Strategic importance of this race');
            $table->json('preparation_strategy')->nullable()->comment('Strategy used to prepare for race');
            $table->json('lessons_learned')->nullable()->comment('Lessons learned from race result');

            // Metadata
            $table->json('race_metadata')->nullable()->comment('Additional race information');
            $table->timestamps();

            // Indexes for performance
            $table->index(['career_id', 'turn_number']);
            $table->index(['character_id', 'race_grade']);
            $table->index('race_grade');
            $table->index('distance_category');
            $table->index('surface');
            $table->index('running_style');
            $table->index('won_race');
            $table->index('finish_position');
            $table->index('is_ura_finale_race');
            $table->index('is_unity_cup_match');
            $table->index(['career_phase', 'race_grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_races');
    }
};
