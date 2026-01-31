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
        Schema::create('ucp_support_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Support card name');
            $table->string('internal_id')->unique()->comment('Internal game ID for support card');
            $table->string('external_source_id')->nullable()->comment('External source ID'); // Consolidated from 2026_01_26_162012
            $table->string('external_source')->nullable()->comment('External source name'); // Consolidated from 2026_01_26_162012
            $table->string('gametora_id')->nullable()->comment('GameTora database ID'); // Consolidated from 2026_01_26_162012
            $table->unsignedInteger('chara_id')->nullable()->comment('Character ID'); // Consolidated from 2026_01_26_162012

            // Card classification
            $table->enum('card_type', ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'])->comment('Support card type');
            $table->enum('rarity', ['R', 'SR', 'SSR'])->comment('Support card rarity');
            $table->tinyInteger('limit_break')->unsigned()->default(0)->comment('Limit break level (0-4 stars)'); // Consolidated from 2026_01_25_103310
            $table->integer('max_level')->default(50)->comment('Maximum level for this card');
            $table->integer('max_limit_break')->default(4)->comment('Maximum limit break level');

            // Character association
            $table->string('character_name')->nullable()->comment('Associated character name (null for friend cards)');
            $table->string('character_internal_id')->nullable()->comment('Associated character internal ID');

            // Training bonuses
            $table->integer('speed_bonus')->default(0)->comment('Speed training bonus');
            $table->integer('stamina_bonus')->default(0)->comment('Stamina training bonus');
            $table->integer('power_bonus')->default(0)->comment('Power training bonus');
            $table->integer('guts_bonus')->default(0)->comment('Guts training bonus');
            $table->integer('wit_bonus')->default(0)->comment('Wit training bonus');

            // Friendship and event bonuses
            $table->integer('friendship_bonus')->default(0)->comment('Friendship gain bonus');
            $table->integer('event_recovery_bonus')->default(0)->comment('Event recovery bonus');
            $table->integer('event_effect_bonus')->default(0)->comment('Event effect bonus');
            $table->integer('training_effect_bonus')->default(0)->comment('Training effect bonus');

            // Special abilities and unique effects
            $table->json('unique_effects')->nullable()->comment('Unique card effects and abilities');
            $table->json('skill_hints_provided')->nullable()->comment('Skills this card can provide hints for');
            $table->json('guaranteed_events')->nullable()->comment('Events guaranteed by this card');
            $table->json('special_conditions')->nullable()->comment('Special activation conditions');

            // Card availability and acquisition
            $table->boolean('is_limited')->default(false)->comment('Whether card is limited/seasonal');
            $table->date('release_date')->nullable()->comment('Card release date');
            $table->date('availability_end')->nullable()->comment('End of availability for limited cards');
            $table->json('acquisition_methods')->nullable()->comment('How to obtain this card');

            // Meta and strategic information
            $table->enum('meta_tier', ['S+', 'S', 'A', 'B', 'C'])->nullable()->comment('Current meta tier ranking');
            $table->json('deck_synergies')->nullable()->comment('Cards that synergize well with this card');
            $table->json('recommended_scenarios')->nullable()->comment('Scenarios where this card excels');
            $table->json('strategic_notes')->nullable()->comment('Strategic usage notes');

            // Performance tracking
            $table->decimal('usage_rate', 5, 2)->nullable()->comment('Usage rate in competitive play');
            $table->decimal('win_rate_contribution', 5, 2)->nullable()->comment('Win rate contribution when used');
            $table->json('performance_data')->nullable()->comment('Performance statistics');

            // Card artwork and presentation
            $table->string('artwork_url')->nullable()->comment('URL to card artwork');
            $table->json('artwork_variants')->nullable()->comment('Alternative artwork versions');
            $table->text('flavor_text')->nullable()->comment('Card flavor text');

            // Status and metadata
            $table->boolean('is_active')->default(true)->comment('Whether card is currently available');
            $table->enum('server_availability', ['jp', 'global', 'both'])->default('both')->comment('Server availability'); // Consolidated from 2026_01_26_162012
            $table->json('card_metadata')->nullable()->comment('Additional card information');
            $table->timestamps();

            // Indexes for performance
            $table->index('card_type');
            $table->index('rarity');
            $table->index(['card_type', 'rarity']);
            $table->index('character_name');
            $table->index('is_limited');
            $table->index('meta_tier');
            $table->index('is_active');
            $table->index('external_source_id'); // Consolidated from 2026_01_26_162012
            $table->index('gametora_id'); // Consolidated from 2026_01_26_162012
            $table->index('chara_id'); // Consolidated from 2026_01_26_162012
            $table->index('server_availability'); // Consolidated from 2026_01_26_162012
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_support_cards');
    }
};
