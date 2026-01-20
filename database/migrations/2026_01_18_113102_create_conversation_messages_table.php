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
        Schema::create('ucp_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ucp_ai_conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');

            // Message content and type
            $table->enum('message_type', ['user', 'ai', 'system', 'tool_call', 'tool_result'])->comment('Type of message');
            $table->text('message_content')->comment('Message content');
            $table->json('message_metadata')->nullable()->comment('Additional message metadata');

            // Agent attribution for multi-agent conversations
            $table->string('agent_id')->nullable()->comment('ID of agent that generated this message');
            $table->string('agent_type')->nullable()->comment('Type of agent (training, career, race, skill, etc.)');
            $table->string('agent_name')->nullable()->comment('Human-readable agent name');
            $table->json('agent_context')->nullable()->comment('Agent-specific context');

            // Tool usage tracking
            $table->json('tools_used')->nullable()->comment('Tools/functions called during message generation');
            $table->json('tool_results')->nullable()->comment('Results from tool calls');
            $table->integer('tool_call_count')->default(0)->comment('Number of tool calls made');

            // Conversation branching
            $table->foreignId('parent_message_id')->nullable()->constrained('ucp_conversation_messages')->onDelete('set null')->comment('Parent message for branching');
            $table->string('branch_id')->nullable()->comment('Branch identifier for alternative paths');
            $table->integer('branch_depth')->default(0)->comment('Depth in conversation tree');
            $table->boolean('is_branch_point')->default(false)->comment('Whether this message is a branching point');
            $table->json('branch_metadata')->nullable()->comment('Metadata about branch (reason, alternatives)');

            // AI model and processing
            $table->string('ai_model_used')->nullable()->comment('Specific AI model used');
            $table->float('processing_time')->nullable()->comment('Processing time in seconds');
            $table->integer('tokens_used')->nullable()->comment('Tokens consumed');
            $table->decimal('cost_estimate', 10, 6)->nullable()->comment('Estimated cost in USD');
            $table->json('model_parameters')->nullable()->comment('Model parameters used');

            // Quality and feedback
            $table->integer('quality_rating')->nullable()->comment('User quality rating (1-5)');
            $table->boolean('is_helpful')->nullable()->comment('Whether user found message helpful');
            $table->text('user_feedback')->nullable()->comment('User feedback on this message');
            $table->json('quality_metrics')->nullable()->comment('Automated quality metrics');

            // Message state and visibility
            $table->enum('status', ['pending', 'completed', 'failed', 'edited', 'deleted'])->default('completed')->comment('Message status');
            $table->boolean('is_visible')->default(true)->comment('Whether message is visible to user');
            $table->boolean('is_pinned')->default(false)->comment('Whether message is pinned');
            $table->boolean('is_bookmarked')->default(false)->comment('Whether message is bookmarked');

            // Timestamps
            $table->timestamp('sent_at')->nullable()->comment('When message was sent');
            $table->timestamp('edited_at')->nullable()->comment('When message was last edited');
            $table->timestamps();

            // Indexes for performance
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'message_type']);
            $table->index(['agent_id', 'agent_type']);
            $table->index(['parent_message_id', 'branch_id']);
            $table->index(['is_branch_point', 'branch_depth']);
            $table->index(['quality_rating', 'is_helpful']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_conversation_messages');
    }
};
