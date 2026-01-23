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
        Schema::create('ucp_user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->string('preference_category')->comment('Category of preference (e.g., "ui", "gameplay", "ai", "notifications")');
            $table->string('preference_key')->comment('Specific preference key');
            $table->json('preference_value')->comment('Preference value (can be any JSON type)');

            // Preference metadata
            $table->enum('value_type', ['string', 'integer', 'float', 'boolean', 'array', 'object'])->comment('Type of the preference value');
            $table->text('description')->nullable()->comment('Description of what this preference controls');
            $table->json('allowed_values')->nullable()->comment('Allowed values for this preference');
            $table->json('default_value')->nullable()->comment('Default value for this preference');

            // Preference scope and context
            $table->enum('scope', ['global', 'character', 'career', 'scenario'])->default('global')->comment('Scope of the preference');
            $table->string('context_id')->nullable()->comment('Context ID (character_id, career_id, etc.) if scoped');
            $table->boolean('is_inherited')->default(false)->comment('Whether preference inherits from parent scope');
            $table->json('inheritance_chain')->nullable()->comment('Chain of inheritance for this preference');

            // Preference validation and constraints
            $table->json('validation_rules')->nullable()->comment('Validation rules for preference value');
            $table->decimal('min_value', 10, 2)->nullable()->comment('Minimum value for numeric preferences');
            $table->decimal('max_value', 10, 2)->nullable()->comment('Maximum value for numeric preferences');
            $table->integer('max_length')->nullable()->comment('Maximum length for string preferences');

            // Preference lifecycle
            $table->timestamp('last_modified_at')->comment('When preference was last modified');
            $table->string('modified_by')->default('user')->comment('Who/what modified the preference');
            $table->json('modification_history')->nullable()->comment('History of preference changes');
            $table->boolean('is_system_managed')->default(false)->comment('Whether preference is managed by system');

            // Preference synchronization
            $table->boolean('sync_across_devices')->default(true)->comment('Whether to sync preference across devices');
            $table->timestamp('last_synced_at')->nullable()->comment('When preference was last synced');
            $table->json('sync_conflicts')->nullable()->comment('Synchronization conflicts if any');
            $table->boolean('has_local_override')->default(false)->comment('Whether local override exists');

            // Preference impact and dependencies
            $table->json('affects_features')->nullable()->comment('Features affected by this preference');
            $table->json('dependent_preferences')->nullable()->comment('Other preferences that depend on this one');
            $table->json('conflicts_with')->nullable()->comment('Preferences that conflict with this one');
            $table->boolean('requires_restart')->default(false)->comment('Whether changing this preference requires restart');

            // Privacy and security
            $table->boolean('is_sensitive')->default(false)->comment('Whether preference contains sensitive data');
            $table->enum('privacy_level', ['public', 'private', 'confidential'])->default('private')->comment('Privacy level of preference');
            $table->boolean('encrypted')->default(false)->comment('Whether preference value is encrypted');
            $table->json('access_permissions')->nullable()->comment('Who can access this preference');

            // UI and presentation
            $table->string('display_name')->nullable()->comment('Human-readable name for UI');
            $table->text('help_text')->nullable()->comment('Help text for users');
            $table->string('ui_component')->nullable()->comment('UI component type for editing');
            $table->json('ui_options')->nullable()->comment('Options for UI component');
            $table->integer('display_order')->nullable()->comment('Order for displaying in UI');

            // Analytics and usage
            $table->integer('access_count')->default(0)->comment('Number of times preference was accessed');
            $table->timestamp('last_accessed_at')->nullable()->comment('When preference was last accessed');
            $table->integer('modification_count')->default(0)->comment('Number of times preference was modified');
            $table->json('usage_analytics')->nullable()->comment('Analytics data for preference usage');

            // Metadata and tags
            $table->json('tags')->nullable()->comment('Tags for categorizing preference');
            $table->json('custom_metadata')->nullable()->comment('Custom metadata for specific use cases');
            $table->text('notes')->nullable()->comment('User notes about preference');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'preference_category'], 'idx_user_pref_category');
            $table->index(['user_id', 'preference_category', 'preference_key'], 'idx_user_pref_key');
            $table->index('preference_category', 'idx_pref_category');
            $table->index('scope', 'idx_pref_scope');
            $table->index('context_id', 'idx_pref_context');
            $table->index('is_system_managed', 'idx_pref_system');
            $table->index('sync_across_devices', 'idx_pref_sync');
            $table->index('is_sensitive', 'idx_pref_sensitive');

            // Unique constraint for user preferences
            $table->unique(['user_id', 'preference_category', 'preference_key', 'scope', 'context_id'], 'unique_user_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_user_preferences');
    }
};
