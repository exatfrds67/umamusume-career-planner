<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance-optimized indexes to advisory tables for faster query execution.
     * These indexes support common query patterns in the Training Advisory System:
     * - Recent recommendations by career and type
     * - Active alerts filtering
     * - Prediction accuracy analysis by model and time range
     */
    public function up(): void
    {
        Schema::table('ucp_advisory_recommendations', function (Blueprint $table) {
            // Composite index for filtering by career, type, and time
            // Supports queries like: "Get recent Speed training recommendations for this career"
            $table->index(['career_id', 'recommendation_type', 'created_at'], 'idx_career_type_time');

            // Index for filtering followed recommendations
            // Supports queries like: "Get recommendations that were actually followed"
            $table->index(['career_id', 'was_followed'], 'idx_career_followed');

            // Index for confidence score filtering
            // Supports queries like: "Get high-confidence recommendations"
            $table->index('confidence_score', 'idx_confidence');
        });

        Schema::table('ucp_critical_alerts', function (Blueprint $table) {
            // Composite index for active alerts by type
            // Supports queries like: "Get all active stamina crisis alerts for this career"
            $table->index(['career_id', 'alert_type', 'was_dismissed'], 'idx_career_type_dismissed');

            // Index for urgency-based queries
            // Supports queries like: "Get alerts that need immediate attention"
            $table->index('turns_until_critical', 'idx_urgency');

            // Index for time-based cleanup
            // Supports queries like: "Find old dismissed alerts to archive"
            $table->index(['was_dismissed', 'dismissed_at'], 'idx_dismissed_time');
        });

        Schema::table('ucp_prediction_accuracy', function (Blueprint $table) {
            // Composite index for accuracy analysis by career and type
            // Supports queries like: "Get all training predictions for this career"
            $table->index(['career_id', 'prediction_type'], 'idx_career_pred_type');

            // Composite index for model performance analysis
            // Supports queries like: "Get accuracy scores for model v1.2 in the last 30 days"
            $table->index(['model_version', 'created_at', 'accuracy_score'], 'idx_model_time_score');

            // Index for turn-based analysis
            // Supports queries like: "Get predictions for turns 20-30"
            $table->index(['career_id', 'turn_number'], 'idx_pred_career_turn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_advisory_recommendations', function (Blueprint $table) {
            $table->dropIndex('idx_career_type_time');
            $table->dropIndex('idx_career_followed');
            $table->dropIndex('idx_confidence');
        });

        Schema::table('ucp_critical_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_career_type_dismissed');
            $table->dropIndex('idx_urgency');
            $table->dropIndex('idx_dismissed_time');
        });

        Schema::table('ucp_prediction_accuracy', function (Blueprint $table) {
            $table->dropIndex('idx_career_pred_type');
            $table->dropIndex('idx_model_time_score');
            $table->dropIndex('idx_pred_career_turn');
        });
    }
};
