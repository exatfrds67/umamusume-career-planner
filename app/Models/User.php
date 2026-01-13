<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'preferences',
        'accessibility_settings',
        'ai_settings',
        'mcp_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user): void {
            if (empty($user->uuid)) {
                $user->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => AsArrayObject::class,
            'accessibility_settings' => AsArrayObject::class,
            'ai_settings' => AsArrayObject::class,
            'mcp_settings' => AsArrayObject::class,
        ];
    }

    /**
     * Get the user's characters.
     *
     * @return HasMany<Character, $this>
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Get the user's careers.
     *
     * @return HasMany<Career, $this>
     */
    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    /**
     * Get the user's AI conversations.
     *
     * @return HasMany<AIConversation, $this>
     */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }

    /**
     * Get the user's preferences.
     *
     * @return HasMany<UserPreference, $this>
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Get the user's system logs.
     *
     * @return HasMany<SystemLog, $this>
     */
    public function systemLogs(): HasMany
    {
        return $this->hasMany(SystemLog::class);
    }

    /**
     * Get all events related to this user (polymorphic).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Event, $this>
     */
    public function events(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Event::class, 'eventable');
    }

    /**
     * Get all system logs related to this user (polymorphic).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<SystemLog, $this>
     */
    public function relatedSystemLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(SystemLog::class, 'loggable');
    }

    /**
     * Get all AI conversations related to this user (polymorphic).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<AIConversation, $this>
     */
    public function relatedAiConversations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AIConversation::class, 'conversationable');
    }

    /**
     * Check if user can access a specific character.
     */
    public function canAccessCharacter(Character $character): bool
    {
        return $this->id === $character->user_id;
    }

    /**
     * Get the user's subscription tier (for future premium features).
     */
    public function getSubscriptionTier(): string
    {
        $settings = is_array($this->ai_settings) ? $this->ai_settings : [];

        return $settings['subscription_tier'] ?? 'free';
    }

    /**
     * Get the user's AI budget limit.
     */
    public function getAIBudgetLimit(): float
    {
        $settings = is_array($this->ai_settings) ? $this->ai_settings : [];

        return $settings['budget_limit'] ?? 10.0;
    }

    /**
     * Get the user's preferred AI model.
     */
    public function getPreferredAIModel(): string
    {
        $settings = is_array($this->ai_settings) ? $this->ai_settings : [];

        return $settings['preferred_model'] ?? 'ollama';
    }

    /**
     * Check if user has accessibility features enabled.
     */
    public function hasAccessibilityFeatures(): bool
    {
        return ! empty($this->accessibility_settings);
    }

    /**
     * Get the user's display name with accessibility support.
     *
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?: 'User #'.$this->id
        );
    }

    /**
     * Get the user's active characters count.
     *
     * @return Attribute<int, never>
     */
    protected function activeCharactersCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->characters()->where('status', 'active')->count()
        );
    }
}
