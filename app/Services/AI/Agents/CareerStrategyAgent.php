<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Career Strategy Agent
 *
 * Specialized MCP-powered agent for long-term career planning, goal optimization,
 * race scheduling, and milestone tracking.
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class CareerStrategyAgent
{
    protected MCPClientService $mcpClient;

    protected string $agentId;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('ai.agents.career_strategy.enabled', true);
        $config = Config::get('ai.agents.career_strategy', []);
        $this->config = \is_array($config) ? $config : [];
        $this->agentId = 'career-strategy-'.uniqid();
    }

    /**
     * Create comprehensive career plan
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     plan: array<string, mixed>,
     *     milestones: array<int, array<string, mixed>>,
     *     race_schedule: array<int, array<string, mixed>>,
     *     training_priorities: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function createCareerPlan(Character $character, array $goals = []): array
    {
        if (! $this->enabled) {
            return $this->getDefaultCareerPlan($character);
        }

        $startTime = microtime(true);

        try {
            // Prepare context
            $context = [
                'character' => $this->getCharacterData($character),
                'goals' => $goals,
                'task' => 'create_career_plan',
            ];

            // Check cache
            $cacheKey = $this->getCacheKey($character->id, $context);
            /** @var array{plan: array<string, mixed>, milestones: array<int, array<string, mixed>>, race_schedule: array<int, array<string, mixed>>, training_priorities: array<string, mixed>, confidence: float, reasoning: string, metadata: array<string, mixed>}|null $cached */
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $planData */
            $planData = isset($response['plan']) && is_array($response['plan']) ? $response['plan'] : [];
            /** @var array<int|string, mixed> $rawMilestones */
            $rawMilestones = isset($response['milestones']) && is_array($response['milestones']) ? $response['milestones'] : [];
            /** @var array<int, array<string, mixed>> $milestones */
            $milestones = array_values($rawMilestones);
            /** @var array<int|string, mixed> $rawRaceSchedule */
            $rawRaceSchedule = isset($response['race_schedule']) && is_array($response['race_schedule']) ? $response['race_schedule'] : [];
            /** @var array<int, array<string, mixed>> $raceSchedule */
            $raceSchedule = array_values($rawRaceSchedule);
            /** @var array<string, mixed> $trainingPriorities */
            $trainingPriorities = isset($response['training_priorities']) && is_array($response['training_priorities']) ? $response['training_priorities'] : [];

            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;
            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Career plan created';

            $plan = [
                'plan' => $planData,
                'milestones' => $milestones,
                'race_schedule' => $raceSchedule,
                'training_priorities' => $trainingPriorities,
                'confidence' => (float) $confidenceRaw,
                'reasoning' => (string) $reasoningRaw,
                'metadata' => [
                    'agent_id' => $this->agentId,
                    'processing_time' => microtime(true) - $startTime,
                    'character_id' => $character->id,
                ],
            ];

            // Cache plan
            Cache::put($cacheKey, $plan, 600); // 10 minutes

            return $plan;
        } catch (\Exception $e) {
            Log::error('[CareerStrategyAgent] Career plan creation failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return $this->getDefaultCareerPlan($character);
        }
    }

    /**
     * Optimize goal priorities based on current progress
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     priorities: array<int, array<string, mixed>>,
     *     recommendations: array<int, string>,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function optimizeGoalPriorities(Character $character, array $goals = []): array
    {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'goals' => $goals,
                'task' => 'optimize_goal_priorities',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<int|string, mixed> $rawPriorities */
            $rawPriorities = isset($response['priorities']) && is_array($response['priorities']) ? $response['priorities'] : [];
            /** @var array<int, array<string, mixed>> $priorities */
            $priorities = array_values($rawPriorities);
            /** @var array<int|string, mixed> $rawRecommendations */
            $rawRecommendations = isset($response['recommendations']) && is_array($response['recommendations']) ? $response['recommendations'] : [];
            /** @var array<int, string> $recommendations */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $recommendations = array_map(static fn (mixed $v): string => (string) $v, array_values($rawRecommendations));

            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;
            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Goals optimized';

            return [
                'priorities' => $priorities,
                'recommendations' => $recommendations,
                'confidence' => (float) $confidenceRaw,
                'reasoning' => (string) $reasoningRaw,
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStrategyAgent] Goal optimization failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'priorities' => [],
                'recommendations' => ['Unable to optimize goals at this time'],
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Goal optimization unavailable',
            ];
        }
    }

    /**
     * Generate race schedule recommendations
     *
     * @param  array<string, mixed>  $constraints
     * @return array{
     *     schedule: array<int, array<string, mixed>>,
     *     reasoning: string,
     *     confidence: float
     * }
     */
    public function generateRaceSchedule(Character $character, array $constraints = []): array
    {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'constraints' => $constraints,
                'task' => 'generate_race_schedule',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<int|string, mixed> $rawSchedule */
            $rawSchedule = isset($response['schedule']) && is_array($response['schedule']) ? $response['schedule'] : [];
            /** @var array<int, array<string, mixed>> $schedule */
            $schedule = array_values($rawSchedule);

            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Schedule generated';
            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;

            return [
                'schedule' => $schedule,
                'reasoning' => (string) $reasoningRaw,
                'confidence' => (float) $confidenceRaw,
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStrategyAgent] Race schedule generation failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'schedule' => [],
                'reasoning' => 'Unable to generate schedule',
                'confidence' => 0.5,
            ];
        }
    }

    /**
     * Track milestone progress and provide recommendations
     *
     * @param  array<string, mixed>  $milestones
     * @return array{
     *     progress: array<string, mixed>,
     *     next_steps: array<int, string>,
     *     warnings: array<int, string>,
     *     confidence: float
     * }
     */
    public function trackMilestoneProgress(Character $character, array $milestones = []): array
    {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'milestones' => $milestones,
                'task' => 'track_milestone_progress',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $progress */
            $progress = isset($response['progress']) && is_array($response['progress']) ? $response['progress'] : [];
            /** @var array<int|string, mixed> $rawNextSteps */
            $rawNextSteps = isset($response['next_steps']) && is_array($response['next_steps']) ? $response['next_steps'] : [];
            /** @var array<int, string> $nextSteps */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $nextSteps = array_map(static fn (mixed $v): string => (string) $v, array_values($rawNextSteps));
            /** @var array<int|string, mixed> $rawWarnings */
            $rawWarnings = isset($response['warnings']) && is_array($response['warnings']) ? $response['warnings'] : [];
            /** @var array<int, string> $warnings */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $warnings = array_map(static fn (mixed $v): string => (string) $v, array_values($rawWarnings));

            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;

            return [
                'progress' => $progress,
                'next_steps' => $nextSteps,
                'warnings' => $warnings,
                'confidence' => (float) $confidenceRaw,
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStrategyAgent] Milestone tracking failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'progress' => [],
                'next_steps' => [],
                'warnings' => [],
                'confidence' => 0.5,
            ];
        }
    }

    /**
     * Get character data for agent context
     *
     * @return array<string, mixed>
     */
    protected function getCharacterData(Character $character): array
    {
        return [
            'id' => $character->id,
            'name' => $character->name,
            'scenario_type' => $character->scenario_type,
            'current_stats' => $character->current_stats,
            'energy_level' => $character->energy_level,
            'mood_status' => $character->mood_status,
            'career_stage' => $character->career_stage,
            'turn_number' => $character->turn_number ?? 0,
        ];
    }

    /**
     * Process request through MCP agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function processWithMCPAgent(array $context): array
    {
        if (! $this->mcpClient->isStrandsAgentsAvailable()) {
            throw new \RuntimeException('MCP strands-agents server not available');
        }

        // Implement actual MCP agent call using strands-agents server
        Log::debug('[CareerStrategyAgent] Processing with MCP agent', [
            'agent_id' => $this->agentId,
            'task' => $context['task'] ?? 'unknown',
        ]);

        try {
            // Use MCP client to invoke strands-agents for career strategy
            $result = $this->mcpClient->executeAgent('career-strategy', $context);

            return [
                'plan' => $result['plan'] ?? [],
                'milestones' => $result['milestones'] ?? [],
                'confidence' => $result['confidence'] ?? 0.85,
                'reasoning' => $result['reasoning'] ?? 'MCP agent analysis completed',
            ];
        } catch (\Exception $e) {
            Log::warning('[CareerStrategyAgent] MCP agent call failed, using fallback', [
                'error' => $e->getMessage(),
            ]);

            // Return simulated response as fallback
            return [
                'plan' => [],
                'milestones' => [],
                'confidence' => 0.85,
                'reasoning' => 'MCP agent analysis completed (fallback)',
            ];
        }
    }

    /**
     * Get default career plan
     *
     * @return array{
     *     plan: array<string, mixed>,
     *     milestones: array<int, array<string, mixed>>,
     *     race_schedule: array<int, array<string, mixed>>,
     *     training_priorities: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultCareerPlan(Character $character): array
    {
        return [
            'plan' => [
                'strategy' => 'balanced',
                'focus' => 'stat_development',
            ],
            'milestones' => [],
            'race_schedule' => [],
            'training_priorities' => [
                'speed' => 0.3,
                'stamina' => 0.25,
                'power' => 0.25,
                'guts' => 0.1,
                'wit' => 0.1,
            ],
            'confidence' => 0.5,
            'reasoning' => 'Default career plan (MCP unavailable)',
            'metadata' => [
                'agent_id' => $this->agentId,
                'fallback' => true,
            ],
        ];
    }

    /**
     * Get cache key
     *
     * @param  array<string, mixed>  $context
     */
    protected function getCacheKey(int $characterId, array $context): string
    {
        $contextHash = md5(json_encode($context) ?: '');

        return "career_strategy_{$characterId}_{$contextHash}";
    }

    /**
     * Check if agent is available
     */
    public function isAvailable(): bool
    {
        return $this->enabled && $this->mcpClient->isStrandsAgentsAvailable();
    }

    /**
     * Get agent status
     *
     * @return array{
     *     enabled: bool,
     *     available: bool,
     *     agent_id: string,
     *     mcp_server: string
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'agent_id' => $this->agentId,
            'mcp_server' => 'strands-agents',
        ];
    }
}
