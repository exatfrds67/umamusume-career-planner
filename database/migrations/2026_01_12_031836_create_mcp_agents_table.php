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
        Schema::create('ucp_mcp_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->string('agent_name')->comment('Name of the MCP agent');
            $table->string('agent_type')->comment('Type of agent (e.g., "career_optimizer", "skill_advisor", "training_planner")');
            $table->string('agent_version')->default('1.0')->comment('Version of the agent');

            // Agent configuration
            $table->json('agent_config')->comment('Agent configuration and parameters');
            $table->json('system_prompt')->nullable()->comment('System prompt for agent behavior');
            $table->json('tool_permissions')->nullable()->comment('Tools this agent is allowed to use');
            $table->json('resource_permissions')->nullable()->comment('Resources this agent can access');

            // Agent capabilities and specialization
            $table->json('specializations')->nullable()->comment('Areas of specialization for this agent');
            $table->json('supported_scenarios')->nullable()->comment('Scenarios this agent can handle');
            $table->json('expertise_domains')->nullable()->comment('Domains of expertise');
            $table->decimal('confidence_threshold', 3, 2)->default(0.70)->comment('Minimum confidence threshold for responses');

            // Agent state and lifecycle
            $table->enum('status', ['active', 'inactive', 'training', 'error', 'maintenance'])->default('inactive')->comment('Current agent status');
            $table->timestamp('last_active_at')->nullable()->comment('When agent was last active');
            $table->timestamp('last_updated_at')->nullable()->comment('When agent configuration was last updated');

            // Performance and learning metrics
            $table->integer('total_interactions')->default(0)->comment('Total number of interactions');
            $table->integer('successful_interactions')->default(0)->comment('Number of successful interactions');
            $table->decimal('success_rate', 5, 2)->nullable()->comment('Success rate percentage');
            $table->decimal('average_response_quality', 3, 2)->nullable()->comment('Average response quality rating');
            $table->json('performance_metrics')->nullable()->comment('Detailed performance metrics');

            // Learning and adaptation
            $table->json('learning_data')->nullable()->comment('Data used for agent learning and improvement');
            $table->json('feedback_history')->nullable()->comment('History of user feedback');
            $table->json('adaptation_log')->nullable()->comment('Log of agent adaptations and improvements');
            $table->timestamp('last_training_at')->nullable()->comment('When agent was last trained');

            // Context and memory management
            $table->json('context_memory')->nullable()->comment('Agent context and memory state');
            $table->json('conversation_history')->nullable()->comment('Recent conversation history');
            $table->json('user_preferences')->nullable()->comment('Learned user preferences');
            $table->integer('memory_limit_mb')->default(100)->comment('Memory limit in MB');

            // Integration and coordination
            $table->json('connected_servers')->nullable()->comment('MCP servers this agent uses');
            $table->json('agent_dependencies')->nullable()->comment('Other agents this agent depends on');
            $table->json('coordination_rules')->nullable()->comment('Rules for coordinating with other agents');
            $table->boolean('can_delegate')->default(false)->comment('Whether agent can delegate to other agents');

            // Security and access control
            $table->json('security_permissions')->nullable()->comment('Security permissions and restrictions');
            $table->json('data_access_rules')->nullable()->comment('Rules for data access');
            $table->boolean('requires_user_approval')->default(false)->comment('Whether agent actions require user approval');
            $table->json('audit_log')->nullable()->comment('Audit log of agent actions');

            // Error handling and debugging
            $table->json('error_history')->nullable()->comment('History of errors and issues');
            $table->text('last_error_message')->nullable()->comment('Last error message');
            $table->timestamp('last_error_at')->nullable()->comment('When last error occurred');
            $table->json('debug_information')->nullable()->comment('Debug information for troubleshooting');

            // Customization and personalization
            $table->json('personality_traits')->nullable()->comment('Agent personality configuration');
            $table->json('communication_style')->nullable()->comment('Preferred communication style');
            $table->json('user_customizations')->nullable()->comment('User-specific customizations');
            $table->boolean('adaptive_behavior')->default(true)->comment('Whether agent adapts behavior based on user');

            // Metadata and tags
            $table->text('description')->nullable()->comment('Description of agent purpose and capabilities');
            $table->json('tags')->nullable()->comment('Tags for categorizing agent');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata for specific use cases');
            $table->text('notes')->nullable()->comment('User notes about agent');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'agent_type']);
            $table->index('agent_type');
            $table->index('status');
            $table->index('last_active_at');
            $table->index('success_rate');
            $table->index('can_delegate');

            // Unique constraint for user-agent combinations
            $table->unique(['user_id', 'agent_name'], 'unique_user_agent_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_mcp_agents');
    }
};
