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
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \ArrayObject<string, mixed> $preferences
 * @property \ArrayObject<string, mixed> $accessibility_settings
 * @property \ArrayObject<string, mixed> $ai_settings
 * @property \ArrayObject<string, mixed> $mcp_settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
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
     * Note: Character model not yet implemented
     *
     * @return HasMany<Character, $this>
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Get the user's careers.
     * Note: Career model not yet implemented
     *
     * @return HasMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function careers(): HasMany
    // {
    //     return $this->hasMany(Career::class);
    // }

    /**
     * Get the user's AI conversations.
     * Note: AIConversation model not yet implemented
     *
     * @return HasMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function aiConversations(): HasMany
    // {
    //     return $this->hasMany(AIConversation::class);
    // }

    /**
     * Get the user's preferences.
     * Note: UserPreference model not yet implemented
     *
     * @return HasMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function userPreferences(): HasMany
    // {
    //     return $this->hasMany(UserPreference::class);
    // }

    /**
     * Get the user's system logs.
     * Note: SystemLog model not yet implemented
     *
     * @return HasMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function systemLogs(): HasMany
    // {
    //     return $this->hasMany(SystemLog::class);
    // }

    /**
     * Get all events related to this user (polymorphic).
     * Note: Event model not yet implemented
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function events(): \Illuminate\Database\Eloquent\Relations\MorphMany
    // {
    //     return $this->morphMany(Event::class, 'eventable');
    // }

    /**
     * Get all system logs related to this user (polymorphic).
     * Note: SystemLog model not yet implemented
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function relatedSystemLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    // {
    //     return $this->morphMany(SystemLog::class, 'loggable');
    // }

    /**
     * Get all AI conversations related to this user (polymorphic).
     * Note: AIConversation model not yet implemented
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    // public function relatedAiConversations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    // {
    //     return $this->morphMany(AIConversation::class, 'conversationable');
    // }

    /**
     * Check if user can access a specific character.
     * Note: Character model not yet implemented
     */
    // public function canAccessCharacter(Character $character): bool
    // {
    //     return $this->id === $character->user_id;
    // }

    /**
     * Get the user's subscription tier (for future premium features).
     */
    public function getSubscriptionTier(): string
    {
        if ($this->ai_settings instanceof \ArrayObject) {
            $settings = $this->ai_settings->getArrayCopy();
            $tier = $settings['subscription_tier'] ?? null;

            return is_string($tier) ? $tier : 'free';
        }

        return 'free';
    }

    /**
     * Get the user's AI budget limit.
     */
    public function getAIBudgetLimit(): float
    {
        if ($this->ai_settings instanceof \ArrayObject) {
            $settings = $this->ai_settings->getArrayCopy();
            $limit = $settings['budget_limit'] ?? null;

            return is_numeric($limit) ? (float) $limit : 10.0;
        }

        return 10.0;
    }

    /**
     * Get the user's preferred AI model.
     */
    public function getPreferredAIModel(): string
    {
        if ($this->ai_settings instanceof \ArrayObject) {
            $settings = $this->ai_settings->getArrayCopy();
            $model = $settings['preferred_model'] ?? null;

            return is_string($model) ? $model : 'ollama';
        }

        return 'ollama';
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
     */
    #[\ReturnTypeWillChange]
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?: 'User #'.$this->id
        );
    }

    /**
     * Get the user's active characters count.
     */
    #[\ReturnTypeWillChange]
    protected function activeCharactersCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->characters()->where('status', 'active')->count()
        );
    }
}
