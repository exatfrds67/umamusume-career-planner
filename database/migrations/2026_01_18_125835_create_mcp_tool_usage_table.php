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
        Schema::create('ucp_mcp_tool_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('ucp_users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('ucp_mcp_agents')->onDelete('set null');
            $table->foreignId('conversation_id')->nullable()->constrained('ucp_ai_conversations')->onDelete('set null');
            $table->foreignId('message_id')->nullable()->constrained('ucp_conversation_messages')->onDelete('set null');

            // Tool identification
            $table->string('server_name')->comment('MCP server name');
            $table->string('tool_name')->comment('Tool name that was executed');
            $table->string('tool_category')->nullable()->comment('Tool category (e.g., "ai", "infrastructure", "data")');

            // Execution details
            $table->json('tool_parameters')->nullable()->comment('Parameters passed to the tool');
            $table->json('tool_result')->nullable()->comment('Result returned by the tool');
            $table->enum('execution_status', ['success', 'failure', 'timeout', 'cancelled'])->default('success');
            $table->text('error_message')->nullable()->comment('Error message if execution failed');

            // Performance metrics
            $table->decimal('execution_time', 8, 3)->comment('Execution time in seconds');
            $table->integer('tokens_used')->nullable()->comment('Tokens used (for AI tools)');
            $table->decimal('cost_estimate', 10, 6)->nullable()->comment('Estimated cost in USD');
            $table->string('cost_model')->nullable()->comment('Cost model used for estimation');

            // Context and metadata
            $table->string('request_id')->nullable()->comment('Request ID for tracking');
            $table->json('context_data')->nullable()->comment('Context data at time of execution');
            $table->json('performance_metrics')->nullable()->comment('Additional performance metrics');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata');

            // Quality and feedback
            $table->integer('quality_rating')->nullable()->comment('Quality rating (1-5)');
            $table->boolean('was_helpful')->nullable()->comment('Whether the tool execution was helpful');
            $table->text('user_feedback')->nullable()->comment('User feedback on tool execution');

            // Timestamps
            $table->timestamp('executed_at')->useCurrent()->comment('When the tool was executed');
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('server_name');
            $table->index('tool_name');
            $table->index('execution_status');
            $table->index('executed_at');
            $table->index(['server_name', 'tool_name']);
            $table->index(['user_id', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_mcp_tool_usage');
    }
};
