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
        Schema::create('ucp_game_races', static function (Blueprint $table): void {
            $table->id();

            // Identification
            $table->string('slug')->unique()->comment('URL-safe identifier, e.g. hopeful-stakes');
            $table->string('name_en')->comment('English race name');
            $table->string('name_jp')->nullable()->comment('Japanese race name');

            // Classification
            $table->enum('grade', ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut'])->index();
            $table->enum('phase', ['junior', 'classic', 'senior', 'all'])->default('all')->index();

            // Track details
            $table->enum('surface', ['turf', 'dirt'])->default('turf')->index();
            $table->unsignedSmallInteger('distance_meters');
            $table->enum('distance_category', ['sprint', 'mile', 'medium', 'long', 'super_long'])->index();
            $table->enum('hand', ['right', 'left', 'straight'])->nullable()->comment('Track direction');
            $table->string('venue')->nullable()->comment('Race venue/track name');

            // Timing within scenario
            $table->enum('season', ['spring', 'summer', 'autumn', 'winter'])->nullable();
            $table->string('month_label')->nullable()->comment('e.g. April Year 2');
            $table->tinyInteger('year_in_scenario')->nullable()->comment('1=Junior, 2=Classic, 3=Senior');

            // Fan / stat requirements
            $table->unsignedInteger('fan_requirement')->default(0)->comment('Minimum fans to enter');
            $table->json('stat_requirements')->nullable()->comment('Recommended stat thresholds');

            // Rewards
            $table->unsignedSmallInteger('fans_reward')->default(0);
            $table->unsignedSmallInteger('sp_reward')->default(0);

            // Metadata
            $table->text('notes')->nullable();
            $table->boolean('is_ura_finale')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_game_races');
    }
};
