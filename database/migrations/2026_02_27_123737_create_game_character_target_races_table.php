<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ucp_game_character_target_races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_character_id')
                ->constrained('ucp_game_characters')
                ->onDelete('cascade');
            $table->foreignId('game_race_id')
                ->constrained('ucp_game_races')
                ->onDelete('cascade');
            $table->enum('race_type', ['required', 'goal', 'story', 'recommended'])
                ->default('recommended')
                ->comment('required=game forces it, goal=character dream, story=unique cutscene, recommended=archetype fit');
            $table->tinyInteger('priority')->default(0)->comment('Sort order within character race list, lower = more important');
            $table->string('notes')->nullable()->comment('Contextual note, e.g. Triple Crown completion');

            $table->unique(['game_character_id', 'game_race_id'], 'ucp_gctr_char_race_unique');
            $table->index('game_character_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ucp_game_character_target_races');
    }
};
