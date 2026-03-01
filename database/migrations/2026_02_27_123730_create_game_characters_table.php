<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ucp_game_characters', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('URL-safe identifier, e.g. special-week');
            $table->string('name_en')->comment('English display name');
            $table->string('name_jp')->nullable()->comment('Japanese display name');
            $table->string('title')->nullable()->comment('Subtitle/epithet, e.g. Our June Bug');
            $table->enum('primary_distance', ['sprint', 'mile', 'medium', 'long', 'super_long'])
                ->default('medium')
                ->comment('Character\'s historically preferred distance category');
            $table->enum('preferred_style', ['escape', 'leader', 'insert', 'tracking'])
                ->nullable()
                ->comment('Natural running style');
            $table->string('real_horse_name')->nullable()->comment('Real-world racehorse this character is based on');
            $table->string('image_path')->nullable()->comment('Local or CDN avatar path');
            $table->json('notes')->nullable()->comment('Extra lore or game notes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ucp_game_characters');
    }
};
