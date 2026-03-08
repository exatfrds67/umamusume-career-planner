<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Tables that have career_phase or career_stage enum columns needing 'ura'
        $careerPhaseAlters = [
            'ucp_careers' => 'current_phase',
            'ucp_training_sessions' => 'career_phase',
            'ucp_races' => 'career_phase',
            'ucp_skill_hints' => 'career_phase',
            'ucp_skill_acquisitions' => 'career_phase',
            'ucp_events' => 'career_phase',
        ];

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ucp_characters MODIFY COLUMN career_stage ENUM('junior', 'classic', 'senior', 'ura') DEFAULT 'junior'");
            foreach ($careerPhaseAlters as $table => $column) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN {$column} ENUM('junior', 'classic', 'senior', 'ura') DEFAULT 'junior'");
            }
        } else {
            // SQLite: change enum columns to string (removes CHECK constraint) to allow 'ura'
            Schema::table('ucp_characters', function (Blueprint $table) {
                $table->string('career_stage')->default('junior')->change();
            });
            foreach ($careerPhaseAlters as $tableName => $column) {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->string($column)->default('junior')->change();
                });
            }
        }

        // Add game_character_id FK for linking to game character master data
        Schema::table('ucp_characters', function (Blueprint $table) use ($driver) {
            if (! Schema::hasColumn('ucp_characters', 'game_character_id')) {
                if ($driver === 'sqlite') {
                    $table->unsignedBigInteger('game_character_id')->nullable();
                } else {
                    $table->foreignId('game_character_id')
                        ->nullable()
                        ->after('scenario_id')
                        ->constrained('ucp_game_characters')
                        ->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('ucp_characters', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $table->dropForeign(['game_character_id']);
            }
            $table->dropColumn('game_character_id');
        });

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ucp_characters MODIFY COLUMN career_stage ENUM('junior', 'classic', 'senior') DEFAULT 'junior'");
            $tables = ['ucp_careers', 'ucp_training_sessions', 'ucp_races', 'ucp_skill_hints', 'ucp_skill_acquisitions', 'ucp_events'];
            foreach ($tables as $table) {
                $column = $table === 'ucp_careers' ? 'current_phase' : 'career_phase';
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN {$column} ENUM('junior', 'classic', 'senior') DEFAULT 'junior'");
            }
        }
    }
};
