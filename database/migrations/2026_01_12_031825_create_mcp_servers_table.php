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
        Schema::create('ucp_mcp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name')->unique()->comment('Unique name for MCP server');
            $table->string('server_type')->comment('Type of MCP server (e.g., "memory", "filesystem", "web")');
            $table->string('server_version')->comment('Version of the MCP server');

            // Server configuration
            $table->json('server_config')->comment('MCP server configuration');
            $table->json('connection_params')->nullable()->comment('Connection parameters for server');
            $table->string('endpoint_url')->nullable()->comment('Server endpoint URL if applicable');
            $table->json('authentication_config')->nullable()->comment('Authentication configuration');

            // Server status and health
            $table->enum('status', ['active', 'inactive', 'error', 'maintenance'])->default('inactive')->comment('Current server status');
            $table->timestamp('last_health_check')->nullable()->comment('Last health check timestamp');
            $table->json('health_status')->nullable()->comment('Detailed health status information');
            $table->integer('consecutive_failures')->default(0)->comment('Number of consecutive connection failures');

            // Server capabilities
            $table->json('supported_tools')->nullable()->comment('Tools supported by this server');
            $table->json('supported_resources')->nullable()->comment('Resources supported by this server');
            $table->json('server_capabilities')->nullable()->comment('Server capabilities and features');
            $table->json('api_schema')->nullable()->comment('API schema for server interactions');

            // Usage and performance metrics
            $table->integer('total_requests')->default(0)->comment('Total requests made to server');
            $table->integer('successful_requests')->default(0)->comment('Number of successful requests');
            $table->integer('failed_requests')->default(0)->comment('Number of failed requests');
            $table->decimal('average_response_time', 8, 3)->nullable()->comment('Average response time in seconds');
            $table->timestamp('last_used_at')->nullable()->comment('When server was last used');

            // Error tracking and debugging
            $table->json('recent_errors')->nullable()->comment('Recent error messages and details');
            $table->text('last_error_message')->nullable()->comment('Last error message received');
            $table->timestamp('last_error_at')->nullable()->comment('When last error occurred');
            $table->json('debug_information')->nullable()->comment('Debug information for troubleshooting');

            // Server lifecycle management
            $table->boolean('auto_start')->default(true)->comment('Whether server should auto-start');
            $table->boolean('auto_restart')->default(true)->comment('Whether server should auto-restart on failure');
            $table->integer('max_restart_attempts')->default(3)->comment('Maximum restart attempts');
            $table->integer('restart_count')->default(0)->comment('Current restart count');

            // Data and resource management
            $table->json('data_sources')->nullable()->comment('Data sources managed by server');
            $table->json('resource_usage')->nullable()->comment('Resource usage statistics');
            $table->integer('memory_usage_mb')->nullable()->comment('Memory usage in MB');
            $table->decimal('cpu_usage_percent', 5, 2)->nullable()->comment('CPU usage percentage');

            // Integration and dependencies
            $table->json('dependent_servers')->nullable()->comment('Other servers this server depends on');
            $table->json('dependent_services')->nullable()->comment('Services that depend on this server');
            $table->json('integration_points')->nullable()->comment('Integration points with other systems');

            // Security and access control
            $table->json('access_permissions')->nullable()->comment('Access permissions and restrictions');
            $table->json('security_settings')->nullable()->comment('Security configuration');
            $table->boolean('requires_authentication')->default(false)->comment('Whether server requires authentication');
            $table->json('allowed_users')->nullable()->comment('Users allowed to access this server');

            // Metadata and configuration
            $table->text('description')->nullable()->comment('Description of server purpose and functionality');
            $table->json('tags')->nullable()->comment('Tags for categorizing server');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata for specific use cases');
            $table->text('notes')->nullable()->comment('Administrative notes about server');
            $table->timestamps();

            // Indexes for performance
            $table->index('server_type');
            $table->index('status');
            $table->index('last_health_check');
            $table->index('last_used_at');
            $table->index('auto_start');
            $table->index('consecutive_failures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_mcp_servers');
    }
};