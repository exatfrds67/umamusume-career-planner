<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\Aptitude;
use App\Models\Career;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Factor;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCardDefinition;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Context Service
 *
 * Manages character and career context awareness across all MCP agents.
 * Provides unified context access for multi-agent workflows.
 */
class AgentContextService
{
    /**
     * Context types
     */
    public const CONTEXT_CHARACTER = 'character';

    public const CONTEXT_CAREER = 'career';

    public const CONTEXT_USER = 'user';

    public const CONTEXT_SESSION = 'session';

    /**
     * Build comprehensive character context for MCP agents
     *
     * @return array<string, mixed>
     */
    public function buildCharacterContext(Character $character): array
    {
        try {
            $cacheKey = "agent_context:character:{$character->id}";

            $result = Cache::remember($cacheKey, 300, function () use ($character): array {
                // Load all relationships
                $character->load([
                    'aptitudes',
                    'factors',
                    'skills',
                    'supportCards',
                    'careers' => function ($query) {
                        $query->latest()->limit(5);
                    },
                ]);

                return [
                    'id' => $character->id,
                    'name' => $character->name,
                    'scenario_type' => $character->scenario_type,
                    'career_stage' => $character->career_stage,
                    'current_turn' => $character->current_turn,

                    // Current stats
                    'stats' => [
                        'speed' => $character->current_stats['speed'] ?? 0,
                        'stamina' => $character->current_stats['stamina'] ?? 0,
                        'power' => $character->current_stats['power'] ?? 0,
                        'guts' => $character->current_stats['guts'] ?? 0,
                        'wit' => $character->current_stats['wit'] ?? 0,
                    ],

                    // State information
                    'state' => [
                        'energy_level' => $character->energy_level,
                        'mood_status' => $character->mood_status,
                        'conditions' => $character->conditions ?? [],
                    ],

                    // Aptitudes
                    'aptitudes' => $character->aptitudes->map(function (Aptitude $aptitude): array {
                        return [
                            'distance_type' => $aptitude->distance_type,
                            'surface_type' => $aptitude->surface_type,
                            'running_style' => $aptitude->running_style,
                            'grade' => $aptitude->grade,
                        ];
                    })->toArray(),

                    // Factors
                    'factors' => $character->factors->map(function (Factor $factor): array {
                        return [
                            'type' => $factor->factor_type,
                            'name' => $factor->factor_name,
                            'level' => $factor->star_level,
                            'stat_bonus' => $factor->stat_bonus,
                            'source' => $factor->source_parent,
                        ];
                    })->toArray(),

                    // Skills
                    'skills' => $character->skills->map(function (Skill $skill): array {
                        return [
                            'id' => $skill->id,
                            'name' => $skill->name,
                            'type' => $skill->skill_type,
                            'rarity' => $skill->rarity,
                            'is_acquired' => $skill->pivot->is_acquired ?? false,
                        ];
                    })->toArray(),

                    // Support cards (through CharacterSupportCard pivot)
                    'support_cards' => $character->supportCards->map(function (CharacterSupportCard $card): array {
                        /** @var SupportCardDefinition|null $definition */
                        $definition = $card->supportCard;

                        return [
                            'id' => $card->id,
                            'name' => $definition->name ?? '',
                            'rarity' => $definition->rarity ?? '',
                            'specialization' => $definition->specialization ?? '',
                            'friendship_level' => $card->friendship_level ?? 0,
                        ];
                    })->toArray(),

                    // Goals
                    'goals' => $character->goals ?? [],

                    // Recent career history
                    'career_history' => $character->careers->map(function (Career $career): array {
                        return [
                            'id' => $career->id,
                            'final_stats' => [
                                'speed' => $career->final_speed,
                                'stamina' => $career->final_stamina,
                                'power' => $career->final_power,
                                'guts' => $career->final_guts,
                                'wit' => $career->final_wit,
                            ],
                            'completed_at' => $career->completed_at instanceof \Illuminate\Support\Carbon ? $career->completed_at->toIso8601String() : null,
                        ];
                    })->toArray(),

                    // Metadata
                    'context_generated_at' => now()->toIso8601String(),
                    'context_version' => '1.0',
                ];
            });

            return is_array($result) ? $result : [];
        } catch (\Exception $e) {
            Log::error('[AgentContext] Failed to build character context', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build career context for MCP agents
     *
     * @return array<string, mixed>
     */
    public function buildCareerContext(?Career $career): array
    {
        if (! $career) {
            return [
                'active' => false,
                'message' => 'No active career',
            ];
        }

        try {
            $cacheKey = "agent_context:career:{$career->id}";

            $result = Cache::remember($cacheKey, 300, function () use ($career): array {
                // Load relationships
                $career->load([
                    'trainingSessions' => function ($query) {
                        $query->orderBy('turn_number', 'desc')->limit(10);
                    },
                    'races' => function ($query) {
                        $query->orderBy('race_date', 'desc')->limit(5);
                    },
                    'events' => function ($query) {
                        $query->orderBy('turn_number', 'desc')->limit(10);
                    },
                ]);

                return [
                    'id' => $career->id,
                    'character_id' => $career->character_id,
                    'scenario_type' => $career->scenario_type,
                    'start_date' => $career->started_at instanceof \Illuminate\Support\Carbon ? $career->started_at->toIso8601String() : null,
                    'current_turn' => $career->current_turn ?? 0,
                    'is_active' => $career->completed_at === null,

                    // Training history
                    'recent_training' => $career->trainingSessions->map(function ($session) {
                        return [
                            'turn' => $session->turn_number,
                            'type' => $session->training_type,
                            'stat_gains' => $session->stat_gains ?? [],
                            'energy_cost' => $session->energy_cost,
                            'skill_hints' => $session->skill_hints_obtained ?? [],
                        ];
                    })->toArray(),

                    // Race history
                    'recent_races' => $career->races->map(function (Race $race): array {
                        return [
                            'name' => $race->race_name,
                            'grade' => $race->race_grade,
                            'distance' => $race->distance_meters,
                            'surface' => $race->surface,
                            'position' => $race->finish_position,
                            'distance_category' => $race->distance_category,
                        ];
                    })->toArray(),

                    // Event history
                    'recent_events' => $career->events->map(function ($event) {
                        return [
                            'turn' => $event->turn_number,
                            'type' => $event->event_type,
                            'name' => $event->event_name,
                            'choice' => $event->chosen_option,
                            'outcome' => $event->choice_effects ?? [],
                        ];
                    })->toArray(),

                    // Performance metrics
                    'metrics' => [
                        'total_training_sessions' => $career->trainingSessions()->count(),
                        'total_races' => $career->races()->count(),
                        'race_wins' => $career->races()->where('final_position', 1)->count(),
                        'total_events' => $career->events()->count(),
                    ],

                    // Metadata
                    'context_generated_at' => now()->toIso8601String(),
                    'context_version' => '1.0',
                ];
            });

            return is_array($result) ? $result : [];
        } catch (\Exception $e) {
            Log::error('[AgentContext] Failed to build career context', [
                'career_id' => $career->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build user context for MCP agents
     *
     * @return array<string, mixed>
     */
    public function buildUserContext(User $user): array
    {
        try {
            $cacheKey = "agent_context:user:{$user->id}";

            $result = Cache::remember($cacheKey, 600, function () use ($user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'preferences' => $this->getUserPreferences($user),
                    'ai_settings' => $this->getUserAISettings($user),
                    'statistics' => $this->getUserStatistics($user),
                    'context_generated_at' => now()->toIso8601String(),
                ];
            });

            return is_array($result) ? $result : [];
        } catch (\Exception $e) {
            Log::error('[AgentContext] Failed to build user context', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build session context for MCP agents
     *
     * @param  array<string, mixed>  $additionalData
     * @return array<string, mixed>
     */
    public function buildSessionContext(string $sessionId, array $additionalData = []): array
    {
        try {
            $cacheKey = "agent_context:session:{$sessionId}";

            $cached = Cache::get($cacheKey);
            $context = is_array($cached) ? $cached : [
                'session_id' => $sessionId,
                'started_at' => now()->toIso8601String(),
                'interactions' => [],
                'agent_history' => [],
            ];

            // Merge additional data
            $context = array_merge($context, $additionalData);
            $context['last_updated_at'] = now()->toIso8601String();

            // Save updated context
            Cache::put($cacheKey, $context, 3600);

            return $context;
        } catch (\Exception $e) {
            Log::error('[AgentContext] Failed to build session context', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build unified context for multi-agent workflows
     *
     * @param  array<string, mixed>  $additionalContext
     * @return array<string, mixed>
     */
    public function buildUnifiedContext(
        Character $character,
        ?Career $career = null,
        ?User $user = null,
        ?string $sessionId = null,
        array $additionalContext = []
    ): array {
        try {
            $context = [
                'character' => $this->buildCharacterContext($character),
                'career' => $this->buildCareerContext($career),
            ];

            if ($user) {
                $context['user'] = $this->buildUserContext($user);
            }

            if ($sessionId) {
                $context['session'] = $this->buildSessionContext($sessionId);
            }

            // Add additional context
            $context = array_merge($context, $additionalContext);

            // Add metadata
            $context['unified_context'] = [
                'generated_at' => now()->toIso8601String(),
                'components' => array_keys($context),
                'version' => '1.0',
            ];

            Log::info('[AgentContext] Unified context built', [
                'character_id' => $character->id,
                'career_id' => $career?->id,
                'user_id' => $user?->id,
                'session_id' => $sessionId,
            ]);

            return $context;
        } catch (\Exception $e) {
            Log::error('[AgentContext] Failed to build unified context', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Invalidate context cache
     */
    public function invalidateContext(string $type, int|string $id): void
    {
        $cacheKey = "agent_context:{$type}:{$id}";
        Cache::forget($cacheKey);

        Log::info('[AgentContext] Context invalidated', [
            'type' => $type,
            'id' => $id,
        ]);
    }

    /**
     * Invalidate all context for a character
     */
    public function invalidateCharacterContext(int $characterId): void
    {
        $this->invalidateContext(self::CONTEXT_CHARACTER, $characterId);

        // Also invalidate related career contexts
        $careers = Career::where('character_id', $characterId)->pluck('id');
        foreach ($careers as $careerId) {
            if (is_int($careerId) || is_string($careerId)) {
                $this->invalidateContext(self::CONTEXT_CAREER, $careerId);
            }
        }
    }

    /**
     * Get user preferences
     *
     * @return array<string, mixed>
     */
    protected function getUserPreferences(User $user): array
    {
        return [
            'ai_provider_preference' => 'ollama', // Default to local
            'agent_verbosity' => 'normal',
            'auto_context_refresh' => true,
            'preferred_agents' => [],
        ];
    }

    /**
     * Get user AI settings
     *
     * @return array<string, mixed>
     */
    protected function getUserAISettings(User $user): array
    {
        return [
            'enable_ollama' => true,
            'enable_bedrock' => false,
            'enable_mcp_agents' => true,
            'max_agent_cost_per_session' => 1.00, // $1 USD
            'preferred_models' => [
                'ollama' => 'llama3.3',
                'bedrock' => 'claude-3-5-sonnet',
            ],
        ];
    }

    /**
     * Get user statistics
     *
     * @return array<string, mixed>
     */
    protected function getUserStatistics(User $user): array
    {
        return [
            'total_characters' => $user->characters()->count(),
            'total_careers' => Career::whereHas('character', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })->count(),
            'total_ai_conversations' => $user->aiConversations()->count(),
            'member_since' => $user->created_at instanceof \Illuminate\Support\Carbon ? $user->created_at->toIso8601String() : (string) $user->created_at,
        ];
    }
}
