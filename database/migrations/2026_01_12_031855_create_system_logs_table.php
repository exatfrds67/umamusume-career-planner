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
        Schema::create('ucp_system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('ucp_users')->onDelete('set null');

            // Log identification and classification
            $table->string('log_level')->comment('Log level (debug, info, warning, error, critical)');
            $table->string('log_category')->comment('Category of log (e.g., "auth", "career", "ai", "mcp", "system")');
            $table->string('log_source')->comment('Source of the log entry (e.g., "web", "api", "cli", "background")');
            $table->string('event_type')->comment('Type of event being logged');

            // Log content
            $table->text('message')->comment('Primary log message');
            $table->json('context_data')->nullable()->comment('Additional context data');
            $table->json('request_data')->nullable()->comment('Request data if applicable');
            $table->json('response_data')->nullable()->comment('Response data if applicable');
            $table->longText('stack_trace')->nullable()->comment('Stack trace for errors');

            // Request and session context
            $table->string('request_id')->nullable()->comment('Unique request identifier');
            $table->string('session_id')->nullable()->comment('Session identifier');
            $table->string('correlation_id')->nullable()->comment('Correlation ID for tracking related events');
            $table->string('user_agent')->nullable()->comment('User agent string');
            $table->ipAddress('ip_address')->nullable()->comment('IP address of request');

            // System and environment context
            $table->string('hostname')->nullable()->comment('Server hostname');
            $table->string('environment')->default('production')->comment('Environment (development, staging, production)');
            $table->string('application_version')->nullable()->comment('Application version');
            $table->json('system_info')->nullable()->comment('System information at time of log');

            // Performance and timing
            $table->decimal('execution_time_ms', 10, 3)->nullable()->comment('Execution time in milliseconds');
            $table->integer('memory_usage_mb')->nullable()->comment('Memory usage in MB');
            $table->decimal('cpu_usage_percent', 5, 2)->nullable()->comment('CPU usage percentage');
            $table->json('performance_metrics')->nullable()->comment('Additional performance metrics');

            // Error and exception details
            $table->string('exception_class')->nullable()->comment('Exception class name');
            $table->string('error_code')->nullable()->comment('Error code');
            $table->integer('error_line')->nullable()->comment('Line number where error occurred');
            $table->string('error_file')->nullable()->comment('File where error occurred');
            $table->json('error_context')->nullable()->comment('Additional error context');

            // Business logic context
            $table->string('entity_type')->nullable()->comment('Type of entity involved (character, career, etc.)');
            $table->string('entity_id')->nullable()->comment('ID of entity involved');
            $table->string('action_performed')->nullable()->comment('Action that was performed');
            $table->json('business_context')->nullable()->comment('Business logic context');

            // Security and audit
            $table->boolean('is_security_event')->default(false)->comment('Whether this is a security-related event');
            $table->boolean('is_audit_event')->default(false)->comment('Whether this is an audit event');
            $table->json('security_context')->nullable()->comment('Security-related context');
            $table->boolean('contains_sensitive_data')->default(false)->comment('Whether log contains sensitive data');

            // Log processing and analysis
            $table->boolean('is_processed')->default(false)->comment('Whether log has been processed');
            $table->timestamp('processed_at')->nullable()->comment('When log was processed');
            $table->json('analysis_results')->nullable()->comment('Results of log analysis');
            $table->json('extracted_metrics')->nullable()->comment('Metrics extracted from log');

            // Alerting and notifications
            $table->boolean('triggered_alert')->default(false)->comment('Whether log triggered an alert');
            $table->json('alert_details')->nullable()->comment('Details of triggered alerts');
            $table->boolean('requires_attention')->default(false)->comment('Whether log requires human attention');
            $table->timestamp('acknowledged_at')->nullable()->comment('When log was acknowledged');

            // Data retention and cleanup
            $table->enum('retention_policy', ['short', 'medium', 'long', 'permanent'])->default('medium')->comment('Data retention policy');
            $table->timestamp('expires_at')->nullable()->comment('When log entry expires');
            $table->boolean('is_archived')->default(false)->comment('Whether log is archived');
            $table->timestamp('archived_at')->nullable()->comment('When log was archived');

            // Metadata and tags
            $table->json('tags')->nullable()->comment('Tags for categorizing and searching logs');
            $table->json('custom_fields')->nullable()->comment('Custom fields for specific use cases');
            $table->text('notes')->nullable()->comment('Additional notes about log entry');
            $table->timestamps();

            // Indexes for performance and querying
            $table->index(['log_level', 'created_at']);
            $table->index(['log_category', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('log_source');
            $table->index('event_type');
            $table->index('request_id');
            $table->index('session_id');
            $table->index('correlation_id');
            $table->index('is_security_event');
            $table->index('is_audit_event');
            $table->index('triggered_alert');
            $table->index('requires_attention');
            $table->index('expires_at');
            $table->index(['entity_type', 'entity_id']);
            $table->index(['created_at', 'log_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_system_logs');
    }
};