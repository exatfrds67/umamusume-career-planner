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
        // Add additional composite indexes for common query patterns
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->index(['user_id', 'scenario_type', 'status'], 'idx_char_user_scenario_status');
            // Note: final stats are stored in JSON, so we'll skip the final_stats index
        });

        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->index(['skill_type', 'meta_tier'], 'idx_skill_type_tier');
            $table->index(['rarity', 'base_sp_cost'], 'idx_skill_rarity_cost');
        });

        Schema::table('ucp_skill_hints', function (Blueprint $table) {
            $table->index(['character_id', 'turn_obtained'], 'idx_hint_char_turn');
            $table->index(['source_type', 'guaranteed_hint'], 'idx_hint_source_guaranteed');
        });

        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->index(['character_id', 'turn_acquired'], 'idx_acq_char_turn');
            $table->index(['acquisition_method', 'is_evolution'], 'idx_acq_method_evolution');
        });

        Schema::table('ucp_careers', function (Blueprint $table) {
            $table->index(['user_id', 'scenario_type', 'status'], 'idx_career_user_scenario_status');
            $table->index(['completed_at', 'win_rate'], 'idx_career_completed_winrate');
        });

        Schema::table('ucp_training_sessions', function (Blueprint $table) {
            $table->index(['career_id', 'career_phase', 'training_type'], 'idx_training_career_phase_type');
            $table->index(['character_id', 'total_stat_points_gained'], 'idx_training_char_gains');
        });

        Schema::table('ucp_races', function (Blueprint $table) {
            $table->index(['career_id', 'race_grade', 'won_race'], 'idx_race_career_grade_won');
            $table->index(['character_id', 'distance_category', 'surface'], 'idx_race_char_distance_surface');
        });

        Schema::table('ucp_support_cards', function (Blueprint $table) {
            $table->index(['card_type', 'rarity', 'meta_tier'], 'idx_card_type_rarity_tier');
            $table->index(['is_limited', 'is_active'], 'idx_card_limited_active');
        });

        Schema::table('ucp_events', function (Blueprint $table) {
            $table->index(['career_id', 'event_type', 'strategic_impact'], 'idx_event_career_type_impact');
            $table->index(['character_id', 'turn_number'], 'idx_event_char_turn');
        });

        Schema::table('ucp_external_data', function (Blueprint $table) {
            $table->index(['data_source', 'data_type', 'is_active'], 'idx_ext_source_type_active');
            $table->index(['last_fetched_at', 'expires_at'], 'idx_ext_fetched_expires');
        });

        Schema::table('ucp_ai_conversations', function (Blueprint $table) {
            $table->index(['user_id', 'conversation_type', 'status'], 'idx_conv_user_type_status');
            $table->index(['last_activity_at', 'status'], 'idx_conv_activity_status');
        });

        Schema::table('ucp_mcp_servers', function (Blueprint $table) {
            $table->index(['server_type', 'status'], 'idx_mcp_server_type_status');
            $table->index(['last_health_check', 'consecutive_failures'], 'idx_mcp_health_failures');
        });

        Schema::table('ucp_mcp_agents', function (Blueprint $table) {
            $table->index(['user_id', 'agent_type', 'status'], 'idx_agent_user_type_status');
            $table->index(['success_rate', 'last_active_at'], 'idx_agent_success_activity');
        });

        Schema::table('ucp_user_preferences', function (Blueprint $table) {
            $table->index(['user_id', 'scope', 'context_id'], 'idx_pref_user_scope_context');
        });

        Schema::table('ucp_system_logs', function (Blueprint $table) {
            $table->index(['log_level', 'log_category', 'created_at'], 'idx_log_level_category_time');
            $table->index(['user_id', 'entity_type', 'entity_id'], 'idx_log_user_entity');
            $table->index(['is_security_event', 'is_audit_event'], 'idx_log_security_audit');
        });

        // Add check constraints for data integrity (MySQL only)
        // Note: Characters table stores stats in JSON format, so we'll skip stat range constraints
        // SQLite doesn't support ALTER TABLE ADD CONSTRAINT for CHECK constraints

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ucp_skills ADD CONSTRAINT chk_sp_cost_positive CHECK (base_sp_cost > 0)');

            DB::statement('ALTER TABLE ucp_careers ADD CONSTRAINT chk_turn_range CHECK (current_turn >= 1 AND current_turn <= 78)');
            DB::statement('ALTER TABLE ucp_careers ADD CONSTRAINT chk_win_rate_range CHECK (win_rate >= 0 AND win_rate <= 100)');

            DB::statement('ALTER TABLE ucp_training_sessions ADD CONSTRAINT chk_energy_range CHECK (energy_before >= 0 AND energy_before <= 100 AND energy_after >= 0 AND energy_after <= 100)');

            DB::statement('ALTER TABLE ucp_races ADD CONSTRAINT chk_finish_position_positive CHECK (finish_position > 0)');
            DB::statement('ALTER TABLE ucp_races ADD CONSTRAINT chk_field_size_positive CHECK (field_size > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraints (MySQL only)
        // Note: Characters table constraints were not added
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ucp_skills DROP CONSTRAINT IF EXISTS chk_sp_cost_positive');
            DB::statement('ALTER TABLE ucp_careers DROP CONSTRAINT IF EXISTS chk_turn_range');
            DB::statement('ALTER TABLE ucp_careers DROP CONSTRAINT IF EXISTS chk_win_rate_range');
            DB::statement('ALTER TABLE ucp_training_sessions DROP CONSTRAINT IF EXISTS chk_energy_range');
            DB::statement('ALTER TABLE ucp_races DROP CONSTRAINT IF EXISTS chk_finish_position_positive');
            DB::statement('ALTER TABLE ucp_races DROP CONSTRAINT IF EXISTS chk_field_size_positive');
        }

        // Drop additional indexes
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropIndex('idx_char_user_scenario_status');
        });

        Schema::table('ucp_skills', function (Blueprint $table) {
            $table->dropIndex('idx_skill_type_tier');
            $table->dropIndex('idx_skill_rarity_cost');
        });

        Schema::table('ucp_skill_hints', function (Blueprint $table) {
            $table->dropIndex('idx_hint_char_turn');
            $table->dropIndex('idx_hint_source_guaranteed');
        });

        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->dropIndex('idx_acq_char_turn');
            $table->dropIndex('idx_acq_method_evolution');
        });

        Schema::table('ucp_careers', function (Blueprint $table) {
            $table->dropIndex('idx_career_user_scenario_status');
            $table->dropIndex('idx_career_completed_winrate');
        });

        Schema::table('ucp_training_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_training_career_phase_type');
            $table->dropIndex('idx_training_char_gains');
        });

        Schema::table('ucp_races', function (Blueprint $table) {
            $table->dropIndex('idx_race_career_grade_won');
            $table->dropIndex('idx_race_char_distance_surface');
        });

        Schema::table('ucp_support_cards', function (Blueprint $table) {
            $table->dropIndex('idx_card_type_rarity_tier');
            $table->dropIndex('idx_card_limited_active');
        });

        Schema::table('ucp_events', function (Blueprint $table) {
            $table->dropIndex('idx_event_career_type_impact');
            $table->dropIndex('idx_event_char_turn');
        });

        Schema::table('ucp_external_data', function (Blueprint $table) {
            $table->dropIndex('idx_ext_source_type_active');
            $table->dropIndex('idx_ext_fetched_expires');
        });

        Schema::table('ucp_ai_conversations', function (Blueprint $table) {
            $table->dropIndex('idx_conv_user_type_status');
            $table->dropIndex('idx_conv_activity_status');
        });

        Schema::table('ucp_mcp_servers', function (Blueprint $table) {
            $table->dropIndex('idx_mcp_server_type_status');
            $table->dropIndex('idx_mcp_health_failures');
        });

        Schema::table('ucp_mcp_agents', function (Blueprint $table) {
            if ($this->indexExists('ucp_mcp_agents', 'idx_agent_user_type_status')) {
                $table->dropIndex('idx_agent_user_type_status');
            }
            if ($this->indexExists('ucp_mcp_agents', 'idx_agent_success_activity')) {
                $table->dropIndex('idx_agent_success_activity');
            }
        });

        Schema::table('ucp_user_preferences', function (Blueprint $table) {
            $table->dropIndex('idx_pref_user_scope_context');
        });

        Schema::table('ucp_system_logs', function (Blueprint $table) {
            $table->dropIndex('idx_log_level_category_time');
            $table->dropIndex('idx_log_user_entity');
            $table->dropIndex('idx_log_security_audit');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains('name', $indexName);
        }

        // MySQL/MariaDB
        $indexes = $connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }
};
