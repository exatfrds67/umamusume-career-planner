<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Performance Optimization Indexes
 *
 * This migration adds composite indexes and additional indexes
 * for frequently accessed data patterns to optimize query performance.
 *
 * @see Requirements: 17.3, 50.2, 50.3
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Characters table - frequently queried by user and scenario
        if (Schema::hasTable('ucp_characters')) {
            Schema::table('ucp_characters', function (Blueprint $table): void {
                // Composite index for user's characters by scenario type
                if (! $this->indexExists('characters', 'idx_characters_user_scenario')) {
                    $table->index(['user_id', 'scenario_type'], 'idx_characters_user_scenario');
                }

                // Index for filtering by career stage
                if (! $this->indexExists('characters', 'idx_characters_career_stage')) {
                    $table->index('career_stage', 'idx_characters_career_stage');
                }

                // Composite index for active characters lookup
                if (! $this->indexExists('characters', 'idx_characters_user_active')) {
                    $table->index(['user_id', 'created_at'], 'idx_characters_user_active');
                }
            });
        }

        // Careers table - frequently queried for analytics
        if (Schema::hasTable('ucp_careers')) {
            Schema::table('ucp_careers', function (Blueprint $table): void {
                // Composite index for character careers by scenario
                if (! $this->indexExists('careers', 'idx_careers_character_scenario')) {
                    $table->index(['character_id', 'scenario_type'], 'idx_careers_character_scenario');
                }

                // Index for filtering by status
                if (! $this->indexExists('careers', 'idx_careers_status')) {
                    $table->index('status', 'idx_careers_status');
                }

                // Composite index for date range queries
                if (! $this->indexExists('careers', 'idx_careers_dates')) {
                    $table->index(['start_date', 'end_date'], 'idx_careers_dates');
                }

                // Index for final grade filtering
                if (! $this->indexExists('careers', 'idx_careers_final_grade')) {
                    $table->index('final_grade', 'idx_careers_final_grade');
                }
            });
        }

        // Training sessions table - high volume, frequently queried
        if (Schema::hasTable('ucp_training_sessions')) {
            Schema::table('ucp_training_sessions', function (Blueprint $table): void {
                // Composite index for career training by turn
                if (! $this->indexExists('training_sessions', 'idx_training_career_turn')) {
                    $table->index(['career_id', 'turn_number'], 'idx_training_career_turn');
                }

                // Index for training type analysis
                if (! $this->indexExists('training_sessions', 'idx_training_type')) {
                    $table->index('training_type', 'idx_training_type');
                }

                // Composite index for Spirit Burst queries (Unity Cup)
                if (! $this->indexExists('training_sessions', 'idx_training_spirit_burst')) {
                    $table->index(['career_id', 'spirit_burst_triggered'], 'idx_training_spirit_burst');
                }
            });
        }

        // Races table - frequently queried for analytics
        if (Schema::hasTable('ucp_races')) {
            Schema::table('ucp_races', function (Blueprint $table): void {
                // Composite index for career races
                if (! $this->indexExists('races', 'idx_races_career_position')) {
                    $table->index(['career_id', 'final_position'], 'idx_races_career_position');
                }

                // Index for race grade filtering
                if (! $this->indexExists('races', 'idx_races_grade')) {
                    $table->index('race_grade', 'idx_races_grade');
                }

                // Index for distance filtering
                if (! $this->indexExists('races', 'idx_races_distance')) {
                    $table->index('distance', 'idx_races_distance');
                }

                // Composite index for surface and weather analysis
                if (! $this->indexExists('races', 'idx_races_surface_weather')) {
                    $table->index(['surface', 'weather'], 'idx_races_surface_weather');
                }
            });
        }

        // Skills table - frequently queried for recommendations
        if (Schema::hasTable('ucp_skills')) {
            Schema::table('ucp_skills', function (Blueprint $table): void {
                // Index for skill type filtering
                if (! $this->indexExists('skills', 'idx_skills_type')) {
                    $table->index('skill_type', 'idx_skills_type');
                }

                // Index for meta tier filtering
                if (! $this->indexExists('skills', 'idx_skills_meta_tier')) {
                    $table->index('meta_tier', 'idx_skills_meta_tier');
                }

                // Composite index for skill category and type
                if (! $this->indexExists('skills', 'idx_skills_category_type')) {
                    $table->index(['skill_category', 'skill_type'], 'idx_skills_category_type');
                }
            });
        }

        // Skill acquisitions table - frequently queried
        if (Schema::hasTable('ucp_skill_acquisitions')) {
            Schema::table('ucp_skill_acquisitions', function (Blueprint $table): void {
                // Composite index for character skill lookups
                if (! $this->indexExists('skill_acquisitions', 'idx_skill_acq_char_skill')) {
                    $table->index(['character_id', 'skill_id'], 'idx_skill_acq_char_skill');
                }

                // Index for career skill tracking
                if (! $this->indexExists('skill_acquisitions', 'idx_skill_acq_career')) {
                    $table->index('career_id', 'idx_skill_acq_career');
                }
            });
        }

        // Support cards table - frequently queried for deck building
        if (Schema::hasTable('ucp_support_cards')) {
            Schema::table('ucp_support_cards', function (Blueprint $table): void {
                // Index for rarity filtering
                if (! $this->indexExists('support_cards', 'idx_support_rarity')) {
                    $table->index('card_rarity', 'idx_support_rarity');
                }

                // Index for specialization filtering
                if (! $this->indexExists('support_cards', 'idx_support_specialization')) {
                    $table->index('specialization', 'idx_support_specialization');
                }

                // Composite index for character support cards
                if (! $this->indexExists('support_cards', 'idx_support_char_position')) {
                    $table->index(['character_id', 'position_slot'], 'idx_support_char_position');
                }
            });
        }

        // AI conversations table - frequently queried for history
        if (Schema::hasTable('ucp_ai_conversations')) {
            Schema::table('ucp_ai_conversations', function (Blueprint $table): void {
                // Composite index for user conversation history
                if (! $this->indexExists('ai_conversations', 'idx_ai_conv_user_created')) {
                    $table->index(['user_id', 'created_at'], 'idx_ai_conv_user_created');
                }

                // Index for model filtering
                if (! $this->indexExists('ai_conversations', 'idx_ai_conv_model')) {
                    $table->index('ai_model_used', 'idx_ai_conv_model');
                }

                // Composite index for character-specific conversations
                if (! $this->indexExists('ai_conversations', 'idx_ai_conv_char_created')) {
                    $table->index(['character_id', 'created_at'], 'idx_ai_conv_char_created');
                }
            });
        }

        // MCP agents table - frequently queried for orchestration
        if (Schema::hasTable('ucp_mcp_agents')) {
            Schema::table('ucp_mcp_agents', function (Blueprint $table): void {
                // Index for status filtering
                if (! $this->indexExists('mcp_agents', 'idx_mcp_agents_status')) {
                    $table->index('status', 'idx_mcp_agents_status');
                }

                // Composite index for server agents
                if (! $this->indexExists('mcp_agents', 'idx_mcp_agents_server_status')) {
                    $table->index(['mcp_server_id', 'status'], 'idx_mcp_agents_server_status');
                }
            });
        }

        // External data table - frequently queried for caching
        if (Schema::hasTable('ucp_external_data')) {
            Schema::table('ucp_external_data', function (Blueprint $table): void {
                // Composite index for data type and source
                if (! $this->indexExists('external_data', 'idx_external_type_source')) {
                    $table->index(['data_type', 'source_api'], 'idx_external_type_source');
                }

                // Index for cache expiration queries
                if (! $this->indexExists('external_data', 'idx_external_cache_expires')) {
                    $table->index('cache_expires', 'idx_external_cache_expires');
                }

                // Index for active data filtering
                if (! $this->indexExists('external_data', 'idx_external_is_active')) {
                    $table->index('is_active', 'idx_external_is_active');
                }
            });
        }

        // Aptitudes table - frequently queried with characters
        if (Schema::hasTable('ucp_aptitudes')) {
            Schema::table('ucp_aptitudes', function (Blueprint $table): void {
                // Composite index for character aptitude lookups
                if (! $this->indexExists('aptitudes', 'idx_aptitudes_char_type')) {
                    $table->index(['character_id', 'aptitude_type'], 'idx_aptitudes_char_type');
                }
            });
        }

        // Factors table - frequently queried with characters
        if (Schema::hasTable('ucp_factors')) {
            Schema::table('ucp_factors', function (Blueprint $table): void {
                // Composite index for character factor lookups
                if (! $this->indexExists('factors', 'idx_factors_char_type')) {
                    $table->index(['character_id', 'factor_type'], 'idx_factors_char_type');
                }

                // Index for factor level filtering
                if (! $this->indexExists('factors', 'idx_factors_level')) {
                    $table->index('factor_level', 'idx_factors_level');
                }
            });
        }

        // Events table - frequently queried for career events
        if (Schema::hasTable('ucp_events')) {
            Schema::table('ucp_events', function (Blueprint $table): void {
                // Composite index for career events by turn
                if (! $this->indexExists('events', 'idx_events_career_turn')) {
                    $table->index(['career_id', 'turn_number'], 'idx_events_career_turn');
                }

                // Index for event type filtering
                if (! $this->indexExists('events', 'idx_events_type')) {
                    $table->index('event_type', 'idx_events_type');
                }
            });
        }

        // User preferences table - frequently queried
        if (Schema::hasTable('ucp_user_preferences')) {
            Schema::table('ucp_user_preferences', function (Blueprint $table): void {
                // Composite index for user preference lookups
                if (! $this->indexExists('user_preferences', 'idx_user_prefs_user_key')) {
                    $table->index(['user_id', 'preference_key'], 'idx_user_prefs_user_key');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Characters table
        if (Schema::hasTable('ucp_characters')) {
            Schema::table('ucp_characters', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_characters_user_scenario');
                $this->dropIndexIfExists($table, 'idx_characters_career_stage');
                $this->dropIndexIfExists($table, 'idx_characters_user_active');
            });
        }

        // Careers table
        if (Schema::hasTable('ucp_careers')) {
            Schema::table('ucp_careers', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_careers_character_scenario');
                $this->dropIndexIfExists($table, 'idx_careers_status');
                $this->dropIndexIfExists($table, 'idx_careers_dates');
                $this->dropIndexIfExists($table, 'idx_careers_final_grade');
            });
        }

        // Training sessions table
        if (Schema::hasTable('ucp_training_sessions')) {
            Schema::table('ucp_training_sessions', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_training_career_turn');
                $this->dropIndexIfExists($table, 'idx_training_type');
                $this->dropIndexIfExists($table, 'idx_training_spirit_burst');
            });
        }

        // Races table
        if (Schema::hasTable('ucp_races')) {
            Schema::table('ucp_races', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_races_career_position');
                $this->dropIndexIfExists($table, 'idx_races_grade');
                $this->dropIndexIfExists($table, 'idx_races_distance');
                $this->dropIndexIfExists($table, 'idx_races_surface_weather');
            });
        }

        // Skills table
        if (Schema::hasTable('ucp_skills')) {
            Schema::table('ucp_skills', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_skills_type');
                $this->dropIndexIfExists($table, 'idx_skills_meta_tier');
                $this->dropIndexIfExists($table, 'idx_skills_category_type');
            });
        }

        // Skill acquisitions table
        if (Schema::hasTable('ucp_skill_acquisitions')) {
            Schema::table('ucp_skill_acquisitions', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_skill_acq_char_skill');
                $this->dropIndexIfExists($table, 'idx_skill_acq_career');
            });
        }

        // Support cards table
        if (Schema::hasTable('ucp_support_cards')) {
            Schema::table('ucp_support_cards', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_support_rarity');
                $this->dropIndexIfExists($table, 'idx_support_specialization');
                $this->dropIndexIfExists($table, 'idx_support_char_position');
            });
        }

        // AI conversations table
        if (Schema::hasTable('ucp_ai_conversations')) {
            Schema::table('ucp_ai_conversations', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_ai_conv_user_created');
                $this->dropIndexIfExists($table, 'idx_ai_conv_model');
                $this->dropIndexIfExists($table, 'idx_ai_conv_char_created');
            });
        }

        // MCP agents table
        if (Schema::hasTable('ucp_mcp_agents')) {
            Schema::table('ucp_mcp_agents', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_mcp_agents_status');
                $this->dropIndexIfExists($table, 'idx_mcp_agents_server_status');
            });
        }

        // External data table
        if (Schema::hasTable('ucp_external_data')) {
            Schema::table('ucp_external_data', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_external_type_source');
                $this->dropIndexIfExists($table, 'idx_external_cache_expires');
                $this->dropIndexIfExists($table, 'idx_external_is_active');
            });
        }

        // Aptitudes table
        if (Schema::hasTable('ucp_aptitudes')) {
            Schema::table('ucp_aptitudes', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_aptitudes_char_type');
            });
        }

        // Factors table
        if (Schema::hasTable('ucp_factors')) {
            Schema::table('ucp_factors', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_factors_char_type');
                $this->dropIndexIfExists($table, 'idx_factors_level');
            });
        }

        // Events table
        if (Schema::hasTable('ucp_events')) {
            Schema::table('ucp_events', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_events_career_turn');
                $this->dropIndexIfExists($table, 'idx_events_type');
            });
        }

        // User preferences table
        if (Schema::hasTable('ucp_user_preferences')) {
            Schema::table('ucp_user_preferences', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'idx_user_prefs_user_key');
            });
        }
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

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropIndex($indexName);
        } catch (\Exception) {
            // Index doesn't exist, ignore
        }
    }
};
