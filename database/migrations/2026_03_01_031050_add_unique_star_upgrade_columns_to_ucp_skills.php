<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Star-based unique skill upgrade system:
     * - Star 1-2: Character has the base unique skill (weaker version)
     * - Star 3: Unique skill upgrades to the full-power version (level 1)
     * - Star 6: Full-power unique skill starts at level 3 instead of level 1
     *
     * This matches the uma.guide data where each unique skill appears twice:
     * once at full power and once as a weaker 200sp "base" version.
     */
    public function up(): void
    {
        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->boolean('unique_star_upgrade')
                ->nullable()
                ->after('unique_skill_max_level')
                ->comment('Whether this unique skill has a star 3 upgrade (base → full power); null = non-unique skill');

            $table->tinyInteger('unique_star6_initial_level')
                ->nullable()
                ->after('unique_star_upgrade')
                ->comment('Starting skill level for star 6 characters (3 for all upgradeable unique skills)');

            $table->json('unique_base_effects')
                ->nullable()
                ->after('unique_star6_initial_level')
                ->comment('Weaker base effects at star 1-2 before the star 3 upgrade (matches the 200sp version on uma.guide)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->dropColumn(['unique_star_upgrade', 'unique_star6_initial_level', 'unique_base_effects']);
        });
    }
};
