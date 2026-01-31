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
        Schema::create('ucp_characters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->foreignId('character_template_id')->nullable()->comment('Reference to base character template');
            $table->foreignId('scenario_id')->nullable()->comment('Current scenario (URA Finale/Unity Cup)');

            // Character basic information
            $table->string('name');
            $table->string('avatar_url', 500)->nullable()->comment('Character avatar/image URL or path'); // Consolidated from 2026_01_16_230138
            $table->enum('scenario_type', ['ura_finale', 'unity_cup'])->default('ura_finale');
            $table->enum('career_stage', ['junior', 'classic', 'senior'])->default('junior');
            $table->integer('current_turn')->default(0)->comment('Current turn (0-72)');

            // Character stats (0-1200 range with priorities)
            $table->json('current_stats')->comment('Speed, Stamina, Power, Guts, Wit with values 0-1200');
            $table->json('stat_priorities')->comment('Priority ratings for each stat (★ to ★★★★★)');
            $table->json('stat_breakpoints')->nullable()->comment('Breakpoint tracking at 901 and 1600');

            // Character state management
            $table->integer('energy_level')->default(100)->comment('Energy percentage 0-100');
            $table->enum('mood_status', ['awful', 'bad', 'normal', 'good', 'great'])->default('normal');
            $table->json('conditions')->nullable()->comment('Current positive/negative conditions');
            $table->integer('days_until_race')->nullable()->comment('Days until next scheduled race');

            // Goals and objectives
            $table->json('goals')->nullable()->comment('Target stat values and race objectives');
            $table->json('race_schedule')->nullable()->comment('Planned race calendar');
            $table->json('training_plan')->nullable()->comment('Long-term training strategy');

            // Inheritance and factors
            $table->json('growth_rates')->nullable()->comment('Inherited growth bonuses +10%, +20%, +30%');
            $table->json('inherited_factors')->nullable()->comment('Blue/Red/Green/White factors from parents');
            $table->json('legacy_parents')->nullable()->comment('2 main parents + 4 grandparents data');

            // Unity Cup specific fields
            $table->json('team_composition')->nullable()->comment('Unity Cup team members and roles');
            $table->json('facility_levels')->nullable()->comment('Training facility levels 1-5');
            $table->json('spirit_burst_data')->nullable()->comment('Spirit Burst gauge and session tracking');

            // Status and metadata
            $table->enum('status', ['active', 'completed', 'retired', 'archived'])->default('active');
            $table->integer('available_sp')->default(0)->comment('Available skill points'); // Consolidated from 2026_01_17_182650
            $table->boolean('is_pinned')->default(false)->comment('Whether character is pinned for quick access'); // Consolidated from 2026_01_28_233916
            $table->boolean('is_seeded')->default(false)->comment('Whether character is from seed data (accessible to all users)'); // Consolidated from 2026_01_28_234849
            $table->json('completion_data')->nullable()->comment('Final stats and grade when completed');
            $table->timestamps();

            // Indexes for performance
            $table->index('uuid');
            $table->index(['user_id', 'scenario_type']);
            $table->index(['user_id', 'status']);
            $table->index('current_turn');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_characters');
    }
};
