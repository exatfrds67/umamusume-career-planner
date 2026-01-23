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
        Schema::create('ucp_careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Career identification
            $table->string('career_name')->comment('User-defined name for this career run');
            $table->enum('scenario_type', ['ura_finale', 'unity_cup'])->comment('Scenario type for this career');
            $table->enum('status', ['planning', 'active', 'completed', 'abandoned'])->default('planning')->comment('Current career status');

            // Career timeline
            $table->integer('current_turn')->default(1)->comment('Current turn number (1-78)');
            $table->enum('current_phase', ['junior', 'classic', 'senior'])->default('junior')->comment('Current career phase');
            $table->date('started_at')->nullable()->comment('When career was started');
            $table->date('completed_at')->nullable()->comment('When career was completed');

            // Final stats and results
            $table->integer('final_speed')->nullable()->comment('Final speed stat');
            $table->integer('final_stamina')->nullable()->comment('Final stamina stat');
            $table->integer('final_power')->nullable()->comment('Final power stat');
            $table->integer('final_guts')->nullable()->comment('Final guts stat');
            $table->integer('final_wit')->nullable()->comment('Final wit stat');
            $table->integer('final_sp')->nullable()->comment('Final skill points');

            // Performance metrics
            $table->integer('total_races_won')->default(0)->comment('Total races won during career');
            $table->integer('total_races_participated')->default(0)->comment('Total races participated in');
            $table->decimal('win_rate', 5, 2)->nullable()->comment('Win rate percentage');
            $table->integer('total_fans_gained')->default(0)->comment('Total fans gained during career');

            // URA Finale specific fields
            $table->boolean('ura_finale_cleared')->nullable()->comment('Whether URA Finale was cleared');
            $table->enum('ura_finale_difficulty', ['easy', 'normal', 'hard'])->nullable()->comment('URA Finale difficulty level');
            $table->json('ura_finale_results')->nullable()->comment('URA Finale race results');

            // Unity Cup specific fields
            $table->integer('unity_cup_points')->nullable()->comment('Unity Cup points earned');
            $table->enum('unity_cup_rank', ['D', 'C', 'B', 'A', 'S'])->nullable()->comment('Final Unity Cup rank');
            $table->json('unity_cup_matches')->nullable()->comment('Unity Cup match results');

            // Training and development tracking
            $table->integer('total_training_sessions')->default(0)->comment('Total training sessions completed');
            $table->integer('total_rest_sessions')->default(0)->comment('Total rest sessions taken');
            $table->integer('total_infirmary_visits')->default(0)->comment('Total infirmary visits');
            $table->integer('total_skill_points_earned')->default(0)->comment('Total SP earned during career');
            $table->integer('total_skill_points_spent')->default(0)->comment('Total SP spent on skills');

            // Support card and factor usage
            $table->json('support_deck')->nullable()->comment('Support cards used in this career');
            $table->json('inheritance_factors')->nullable()->comment('Inheritance factors used');
            $table->json('rental_factors')->nullable()->comment('Rental factors used');

            // Strategic notes and metadata
            $table->text('career_notes')->nullable()->comment('User notes about this career run');
            $table->json('strategic_goals')->nullable()->comment('Strategic goals and objectives');
            $table->json('lessons_learned')->nullable()->comment('Lessons learned from this career');
            $table->json('career_metadata')->nullable()->comment('Additional career information');

            // Performance analysis
            $table->decimal('efficiency_rating', 3, 2)->nullable()->comment('Career efficiency rating 0.00-10.00');
            $table->json('performance_analysis')->nullable()->comment('Detailed performance analysis');
            $table->json('improvement_suggestions')->nullable()->comment('AI-generated improvement suggestions');

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['character_id', 'scenario_type']);
            $table->index('scenario_type');
            $table->index('status');
            $table->index('current_phase');
            $table->index(['started_at', 'completed_at']);
            $table->index('win_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_careers');
    }
};
