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
        Schema::create('ucp_external_data', function (Blueprint $table) {
            $table->id();

            // Data source identification
            $table->string('data_source')->comment('Source of the external data (e.g., "gamewith", "appmedia", "wiki")');
            $table->string('data_type')->comment('Type of data (e.g., "character_stats", "skill_data", "support_card_info")');
            $table->string('data_key')->comment('Unique key for this data entry');
            $table->string('data_version')->default('1.0')->comment('Version of the data format');

            // Data content
            $table->json('data_content')->comment('The actual external data content');
            $table->text('data_description')->nullable()->comment('Description of what this data contains');
            $table->json('data_schema')->nullable()->comment('Schema definition for the data content');

            // Data freshness and validation
            $table->timestamp('last_fetched_at')->comment('When this data was last fetched from source');
            $table->timestamp('last_validated_at')->nullable()->comment('When this data was last validated');
            $table->boolean('is_validated')->default(false)->comment('Whether data has been validated');
            $table->json('validation_errors')->nullable()->comment('Validation errors if any');

            // Data source metadata
            $table->string('source_url')->nullable()->comment('URL where data was fetched from');
            $table->string('source_api_version')->nullable()->comment('API version used to fetch data');
            $table->json('fetch_metadata')->nullable()->comment('Metadata about the fetch operation');
            $table->integer('fetch_attempt_count')->default(1)->comment('Number of fetch attempts');

            // Data usage and integration
            $table->json('related_entities')->nullable()->comment('Internal entities this data relates to');
            $table->boolean('is_integrated')->default(false)->comment('Whether data has been integrated into system');
            $table->timestamp('integrated_at')->nullable()->comment('When data was integrated');
            $table->json('integration_log')->nullable()->comment('Log of integration process');

            // Data quality and reliability
            $table->decimal('confidence_score', 3, 2)->nullable()->comment('Confidence score for data accuracy (0.00-1.00)');
            $table->enum('data_quality', ['excellent', 'good', 'fair', 'poor', 'unknown'])->default('unknown')->comment('Assessed data quality');
            $table->json('quality_metrics')->nullable()->comment('Detailed quality assessment metrics');
            $table->integer('usage_count')->default(0)->comment('How many times this data has been used');

            // Data lifecycle management
            $table->boolean('is_active')->default(true)->comment('Whether this data is currently active');
            $table->boolean('is_deprecated')->default(false)->comment('Whether this data is deprecated');
            $table->timestamp('expires_at')->nullable()->comment('When this data expires');
            $table->enum('update_frequency', ['real_time', 'hourly', 'daily', 'weekly', 'monthly', 'manual'])->default('manual')->comment('How frequently this data should be updated');

            // Data transformation and processing
            $table->json('transformation_rules')->nullable()->comment('Rules for transforming this data');
            $table->json('processing_log')->nullable()->comment('Log of data processing operations');
            $table->boolean('requires_processing')->default(false)->comment('Whether data needs processing before use');
            $table->timestamp('last_processed_at')->nullable()->comment('When data was last processed');

            // Data relationships and dependencies
            $table->json('data_dependencies')->nullable()->comment('Other data this entry depends on');
            $table->json('dependent_data')->nullable()->comment('Data that depends on this entry');
            $table->string('parent_data_id')->nullable()->comment('Parent data entry if this is derived data');
            $table->json('child_data_ids')->nullable()->comment('Child data entries derived from this');

            // Error handling and monitoring
            $table->json('error_log')->nullable()->comment('Log of errors encountered with this data');
            $table->integer('error_count')->default(0)->comment('Number of errors encountered');
            $table->timestamp('last_error_at')->nullable()->comment('When last error occurred');
            $table->boolean('has_critical_errors')->default(false)->comment('Whether data has critical errors');

            // Metadata and tags
            $table->json('tags')->nullable()->comment('Tags for categorizing and searching data');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata for specific use cases');
            $table->text('notes')->nullable()->comment('Human-readable notes about this data');
            $table->timestamps();

            // Indexes for performance
            $table->index(['data_source', 'data_type']);
            $table->index(['data_type', 'data_key']);
            $table->index('data_source');
            $table->index('is_active');
            $table->index('is_validated');
            $table->index('last_fetched_at');
            $table->index('expires_at');
            $table->index('update_frequency');
            $table->index('is_deprecated');

            // Unique constraint for data identification
            $table->unique(['data_source', 'data_type', 'data_key'], 'unique_external_data_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_external_data');
    }
};
