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
        Schema::create('ucp_aptitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');

            // Distance aptitudes (fixed talent ratings G through SS)
            $table->enum('sprint_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Sprint 1000-1400m aptitude');
            $table->enum('mile_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Mile 1401-1800m aptitude');
            $table->enum('medium_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Medium 1801-2400m aptitude');
            $table->enum('long_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Long 2401m+ aptitude');

            // Surface aptitudes
            $table->enum('turf_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Turf surface aptitude');
            $table->enum('dirt_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Dirt surface aptitude');

            // Running style aptitudes
            $table->enum('front_runner_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Front Runner style aptitude');
            $table->enum('pace_chaser_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Pace Chaser style aptitude');
            $table->enum('late_surger_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Late Surger style aptitude');
            $table->enum('end_closer_aptitude', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('End Closer style aptitude');

            // Metadata
            $table->json('aptitude_notes')->nullable()->comment('Additional aptitude information and analysis');
            $table->timestamps();

            // Indexes and constraints
            $table->unique('character_id', 'unique_character_aptitudes');
            $table->index('character_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_aptitudes');
    }
};
