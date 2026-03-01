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
        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->string('character_exclusive')->nullable()->after('is_evolution')
                ->comment('Character name this skill belongs to exclusively (e.g. "Vodka", "Oguri Cap")');
            $table->tinyInteger('unique_skill_max_level')->nullable()->after('character_exclusive')
                ->comment('Maximum level for leveling unique skills (1-4); null for non-leveling skills');
            $table->string('condition_marker', 5)->nullable()->after('unique_skill_max_level')
                ->comment('Condition tier marker: ○ = normal conditional, ◎ = gold/rare skill');

            $table->index('character_exclusive');
            $table->index('condition_marker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->dropIndex(['character_exclusive']);
            $table->dropIndex(['condition_marker']);
            $table->dropColumn(['character_exclusive', 'unique_skill_max_level', 'condition_marker']);
        });
    }
};
