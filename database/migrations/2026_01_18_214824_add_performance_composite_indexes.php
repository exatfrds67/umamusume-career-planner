<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Optimization: Composite Indexes
 *
 * Adds composite indexes for high-traffic query patterns:
 * - Careers: character_id + end_date (history lookups)
 * - Training Sessions: career_id + turn_number (turn-by-turn retrieval)
 * - Skill Acquisitions: character_id + is_active (quick filtering)
 * - Races: career_id + position (race analysis)
 *
 * Task 6.1
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Careers: optimize history lookups by character and date range
        Schema::table('ucp_careers', function (Blueprint $table) {
            if (! Schema::hasIndex('ucp_careers', 'idx_careers_char_completed')) {
                $table->index(['character_id', 'completed_at'], 'idx_careers_char_completed');
            }
            if (! Schema::hasIndex('ucp_careers', 'idx_careers_char_status')) {
                $table->index(['character_id', 'status'], 'idx_careers_char_status');
            }
        });

        // Training Sessions: optimize turn-by-turn retrieval
        Schema::table('ucp_training_sessions', function (Blueprint $table) {
            if (! Schema::hasIndex('ucp_training_sessions', 'idx_sessions_career_turn')) {
                $table->index(['career_id', 'turn_number'], 'idx_sessions_career_turn');
            }
            if (! Schema::hasIndex('ucp_training_sessions', 'idx_sessions_char_type')) {
                $table->index(['character_id', 'training_type'], 'idx_sessions_char_type');
            }
        });

        // Skill Acquisitions: optimize active skill filtering
        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            if (! Schema::hasIndex('ucp_skill_acquisitions', 'idx_acquisitions_char_active')) {
                $table->index(['character_id', 'is_active'], 'idx_acquisitions_char_active');
            }
            if (! Schema::hasIndex('ucp_skill_acquisitions', 'idx_acquisitions_skill_evo')) {
                $table->index(['skill_id', 'is_evolution'], 'idx_acquisitions_skill_evo');
            }
        });

        // Races: optimize race analysis by career
        Schema::table('ucp_races', function (Blueprint $table) {
            if (! Schema::hasIndex('ucp_races', 'idx_races_career_finish')) {
                $table->index(['career_id', 'finish_position'], 'idx_races_career_finish');
            }
            if (! Schema::hasIndex('ucp_races', 'idx_races_char_distance')) {
                $table->index(['character_id', 'distance_meters'], 'idx_races_char_distance');
            }
        });

        // Support Card Deck: optimize deck lookups
        Schema::table('character_support_cards', function (Blueprint $table) {
            if (! Schema::hasIndex('character_support_cards', 'idx_deck_char_position')) {
                $table->index(['character_id', 'position_slot'], 'idx_deck_char_position');
            }
        });

        // OCR Extractions: optimize duplicate detection
        Schema::table('ucp_ocr_extractions', function (Blueprint $table) {
            if (! Schema::hasIndex('ucp_ocr_extractions', 'idx_ocr_user_status')) {
                $table->index(['user_id', 'status'], 'idx_ocr_user_status');
            }
            if (! Schema::hasIndex('ucp_ocr_extractions', 'idx_ocr_hash')) {
                $table->index(['image_hash'], 'idx_ocr_hash');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_careers', function (Blueprint $table) {
            $table->dropIndex('idx_careers_char_completed');
            $table->dropIndex('idx_careers_char_status');
        });

        Schema::table('ucp_training_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_career_turn');
            $table->dropIndex('idx_sessions_char_type');
        });

        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->dropIndex('idx_acquisitions_char_active');
            $table->dropIndex('idx_acquisitions_skill_evo');
        });

        Schema::table('ucp_races', function (Blueprint $table) {
            $table->dropIndex('idx_races_career_finish');
            $table->dropIndex('idx_races_char_distance');
        });

        Schema::table('character_support_cards', function (Blueprint $table) {
            $table->dropIndex('idx_deck_char_position');
        });

        Schema::table('ucp_ocr_extractions', function (Blueprint $table) {
            $table->dropIndex('idx_ocr_user_status');
            $table->dropIndex('idx_ocr_hash');
        });
    }
};
