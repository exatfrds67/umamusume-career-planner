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
        Schema::table('ucp_mcp_agents', function (Blueprint $table) {
            // Add AgentCore-specific fields
            $table->string('agent_id')->unique()->after('id')->comment('Unique agent identifier from AgentCore');
            $table->string('name')->after('agent_id')->comment('Agent name');
            $table->string('type')->after('name')->comment('Agent type');
            $table->string('model')->after('type')->comment('AI model used by agent');
            $table->text('instructions')->after('model')->comment('Agent instructions');
            $table->json('tools')->nullable()->after('instructions')->comment('Tools available to agent');
            $table->json('memory_config')->nullable()->after('tools')->comment('Memory configuration');
            $table->json('guardrails')->nullable()->after('memory_config')->comment('Agent guardrails');
            $table->json('metadata')->nullable()->after('guardrails')->comment('Additional metadata');
            $table->string('health_status')->default('unknown')->after('status')->comment('Agent health status');
            $table->float('deployment_time')->default(0)->after('health_status')->comment('Deployment time in seconds');
            $table->timestamp('last_health_check')->nullable()->after('last_active_at')->comment('Last health check timestamp');
            $table->timestamp('terminated_at')->nullable()->after('last_health_check')->comment('Agent termination timestamp');

            // Make user_id and old fields nullable for backward compatibility
            $table->foreignId('user_id')->nullable()->change();
            $table->string('agent_name')->nullable()->change();
            $table->string('agent_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_mcp_agents', function (Blueprint $table) {
            $table->dropColumn([
                'agent_id',
                'name',
                'type',
                'model',
                'instructions',
                'tools',
                'memory_config',
                'guardrails',
                'metadata',
                'health_status',
                'deployment_time',
                'last_health_check',
                'terminated_at',
            ]);
        });
    }
};
