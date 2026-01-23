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
        Schema::create('ucp_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->string('conversation_id')->unique()->comment('Unique identifier for conversation session');

            // Conversation context
            $table->enum('conversation_type', ['career_planning', 'skill_optimization', 'training_advice', 'race_strategy', 'general_help', 'debugging'])->comment('Type of conversation');
            $table->string('conversation_title')->nullable()->comment('User-defined or AI-generated conversation title');
            $table->json('context_entities')->nullable()->comment('Related entities (character, career, etc.)');

            // Conversation state
            $table->enum('status', ['active', 'paused', 'completed', 'archived'])->default('active')->comment('Current conversation status');
            $table->integer('message_count')->default(0)->comment('Total number of messages in conversation');
            $table->timestamp('last_activity_at')->nullable()->comment('Last activity timestamp');
            $table->timestamp('started_at')->nullable()->comment('When conversation started');
            $table->timestamp('ended_at')->nullable()->comment('When conversation ended');

            // AI model and configuration
            $table->string('ai_model')->comment('AI model used for this conversation');
            $table->string('ai_version')->comment('Version of AI model');
            $table->json('ai_configuration')->nullable()->comment('AI model configuration and parameters');
            $table->json('system_prompt')->nullable()->comment('System prompt used for conversation');

            // Conversation metadata
            $table->json('conversation_summary')->nullable()->comment('AI-generated conversation summary');
            $table->json('key_topics')->nullable()->comment('Key topics discussed in conversation');
            $table->json('recommendations_made')->nullable()->comment('Recommendations provided by AI');
            $table->json('user_feedback')->nullable()->comment('User feedback on AI responses');

            // Performance and quality metrics
            $table->decimal('user_satisfaction_rating', 3, 2)->nullable()->comment('User satisfaction rating (0.00-10.00)');
            $table->integer('helpful_responses')->default(0)->comment('Number of responses marked as helpful');
            $table->integer('unhelpful_responses')->default(0)->comment('Number of responses marked as unhelpful');
            $table->json('quality_metrics')->nullable()->comment('Conversation quality metrics');

            // Privacy and data handling
            $table->boolean('contains_sensitive_data')->default(false)->comment('Whether conversation contains sensitive information');
            $table->json('data_retention_policy')->nullable()->comment('Data retention policy for this conversation');
            $table->boolean('user_consented_storage')->default(true)->comment('Whether user consented to data storage');
            $table->timestamp('scheduled_deletion_at')->nullable()->comment('When conversation is scheduled for deletion');

            // Integration and workflow
            $table->json('workflow_state')->nullable()->comment('Current workflow state if applicable');
            $table->json('action_items')->nullable()->comment('Action items generated from conversation');
            $table->json('follow_up_tasks')->nullable()->comment('Follow-up tasks for user or system');
            $table->boolean('requires_human_review')->default(false)->comment('Whether conversation needs human review');

            // Metadata and tags
            $table->json('tags')->nullable()->comment('Tags for categorizing conversation');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata for specific use cases');
            $table->text('notes')->nullable()->comment('User or system notes about conversation');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index('conversation_type');
            $table->index('status');
            $table->index('last_activity_at');
            $table->index('ai_model');
            $table->index('contains_sensitive_data');
            $table->index('requires_human_review');
            $table->index('scheduled_deletion_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_ai_conversations');
    }
};
