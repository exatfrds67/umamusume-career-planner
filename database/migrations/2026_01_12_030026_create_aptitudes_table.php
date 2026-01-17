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

            // Flexible aptitude schema - one row per aptitude type
            $table->string('distance_type')->nullable()->comment('Distance type: sprint, mile, medium, long');
            $table->string('surface_type')->nullable()->comment('Surface type: turf, dirt');
            $table->string('running_style')->nullable()->comment('Running style: front_runner, pace_chaser, late_surger, end_closer');
            $table->enum('grade', ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'])->comment('Aptitude grade');

            $table->timestamps();

            // Indexes
            $table->index('character_id');
            $table->index(['character_id', 'distance_type']);
            $table->index(['character_id', 'surface_type']);
            $table->index(['character_id', 'running_style']);
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
