<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Preference Model
 *
 * Manages user preferences for agents, MCP servers, and workflow templates.
 *
 * @property int $id
 * @property int $user_id
 * @property string $preference_category
 * @property string $preference_key
 * @property array $preference_value
 * @property string $value_type
 * @property string|null $description
 * @property array|null $allowed_values
 * @property array|null $default_value
 * @property string $scope
 * @property string|null $context_id
 * @property bool $is_inherited
 * @property array|null $inheritance_chain
 * @property array|null $validation_rules
 * @property float|null $min_value
 * @property float|null $max_value
 * @property int|null $max_length
 * @property \Illuminate\Support\Carbon $last_modified_at
 * @property string $modified_by
 * @property array|null $modification_history
 * @property bool $is_system_managed
 * @property bool $sync_across_devices
 * @property \Illuminate\Support\Carbon|null $last_synced_at
 * @property array|null $sync_conflicts
 * @property bool $has_local_override
 * @property array|null $affects_features
 * @property array|null $dependent_preferences
 * @property array|null $conflicts_with
 * @property bool $requires_restart
 * @property bool $is_sensitive
 * @property string $privacy_level
 * @property bool $encrypted
 * @property array|null $access_permissions
 * @property string|null $display_name
 * @property string|null $help_text
 * @property string|null $ui_component
 * @property array|null $ui_options
 * @property int|null $display_order
 * @property int $access_count
 * @property \Illuminate\Support\Carbon|null $last_accessed_at
 * @property int $modification_count
 * @property array|null $usage_analytics
 * @property array|null $tags
 * @property array|null $custom_metadata
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UserPreference extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_user_preferences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'preference_category',
        'preference_key',
        'preference_value',
        'value_type',
        'description',
        'allowed_values',
        'default_value',
        'scope',
        'context_id',
        'is_inherited',
        'inheritance_chain',
        'validation_rules',
        'min_value',
        'max_value',
        'max_length',
        'last_modified_at',
        'modified_by',
        'modification_history',
        'is_system_managed',
        'sync_across_devices',
        'last_synced_at',
        'sync_conflicts',
        'has_local_override',
        'affects_features',
        'dependent_preferences',
        'conflicts_with',
        'requires_restart',
        'is_sensitive',
        'privacy_level',
        'encrypted',
        'access_permissions',
        'display_name',
        'help_text',
        'ui_component',
        'ui_options',
        'display_order',
        'access_count',
        'last_accessed_at',
        'modification_count',
        'usage_analytics',
        'tags',
        'custom_metadata',
        'notes',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'scope' => 'global',
        'is_inherited' => false,
        'modified_by' => 'user',
        'is_system_managed' => false,
        'sync_across_devices' => true,
        'has_local_override' => false,
        'requires_restart' => false,
        'is_sensitive' => false,
        'privacy_level' => 'private',
        'encrypted' => false,
        'access_count' => 0,
        'modification_count' => 0,
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'preference_value' => 'array',
            'allowed_values' => 'array',
            'default_value' => 'array',
            'is_inherited' => 'boolean',
            'inheritance_chain' => 'array',
            'validation_rules' => 'array',
            'min_value' => 'float',
            'max_value' => 'float',
            'max_length' => 'integer',
            'last_modified_at' => 'datetime',
            'modification_history' => 'array',
            'is_system_managed' => 'boolean',
            'sync_across_devices' => 'boolean',
            'last_synced_at' => 'datetime',
            'sync_conflicts' => 'array',
            'has_local_override' => 'boolean',
            'affects_features' => 'array',
            'dependent_preferences' => 'array',
            'conflicts_with' => 'array',
            'requires_restart' => 'boolean',
            'is_sensitive' => 'boolean',
            'encrypted' => 'boolean',
            'access_permissions' => 'array',
            'ui_options' => 'array',
            'display_order' => 'integer',
            'access_count' => 'integer',
            'last_accessed_at' => 'datetime',
            'modification_count' => 'integer',
            'usage_analytics' => 'array',
            'tags' => 'array',
            'custom_metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the preference.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get preference value with default fallback.
     */
    public function getValue(mixed $default = null): mixed
    {
        return $this->preference_value ?? $this->default_value ?? $default;
    }

    /**
     * Set preference value.
     */
    public function setValue(mixed $value): void
    {
        $this->preference_value = is_array($value) ? $value : (array) $value;
        $this->last_modified_at = now();
        $this->increment('modification_count', 1);

        // Add to modification history
        $history = $this->modification_history ?? [];
        $history[] = [
            'value' => $value,
            'modified_at' => now()->toIso8601String(),
            'modified_by' => $this->modified_by,
        ];
        $this->modification_history = array_slice($history, -10); // Keep last 10 changes

        $this->save();
    }

    /**
     * Record access to this preference.
     */
    public function recordAccess(): void
    {
        $this->increment('access_count', 1);
        $this->last_accessed_at = now();
        $this->save();
    }

    /**
     * Check if preference is for MCP configuration.
     */
    public function isMCPPreference(): bool
    {
        return $this->preference_category === 'mcp';
    }

    /**
     * Check if preference is for AI configuration.
     */
    public function isAIPreference(): bool
    {
        return $this->preference_category === 'ai';
    }

    /**
     * Check if preference is for workflow configuration.
     */
    public function isWorkflowPreference(): bool
    {
        return $this->preference_category === 'workflow';
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('preference_category', $category);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get a specific preference.
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('preference_key', $key);
    }

    /**
     * Scope a query to filter by scope.
     */
    public function scopeScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Scope a query to filter by context.
     */
    public function scopeContext($query, string $contextId)
    {
        return $query->where('context_id', $contextId);
    }

    /**
     * Scope a query to only include system-managed preferences.
     */
    public function scopeSystemManaged(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_system_managed', true);
    }

    /**
     * Scope a query to only include user-managed preferences.
     */
    public function scopeUserManaged(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_system_managed', false);
    }

    /**
     * Scope a query to only include sensitive preferences.
     */
    public function scopeSensitive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_sensitive', true);
    }
}
