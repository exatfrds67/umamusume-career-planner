<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Training Optimization Agent
 *
 * Specialized MCP-powered agent for complex training sequence planning with stat gain predictions,
 * energy management, and scenario-specific mechanics (URA Finale vs Unity Cup).
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class TrainingOptimizationAgent
{
    protected MCPClientService $mcpClient;

    protected string $agentId;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('ai.agents.training_optimization.enabled', true);
        $config = Config::get('ai.agents.training_optimization', []);
        $this->config = \is_array($config) ? $config : [];
        $this->agentId = 'training-optimization-'.uniqid();
    }

    /**
     * Analyze training options and provide optimized recommendations
     *
     * @param  array<string, mixed>  $trainingOptions
     * @param  array<string, mixed>  $goals
     * @return array{
     *     recommendations: array<int, array<string, mixed>>,
     *     analysis: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata: array<string, mixed>
     * }
     */
    public function analyzeTrainingOptions(
        Character $character,
        array $trainingOptions,
        array $goals = []
    ): array {
        if (! $this->enabled) {
            return $this->getDefaultRecommendations($trainingOptions, $character);
        }

        $startTime = microtime(true);

        try {
            // Prepare context for agent
            $context = $this->prepareContext($character, $trainingOptions, $goals);

            // Check cache first
            $cacheKey = $this->getCacheKey($character->id, $context);
            $cached = Cache::get($cacheKey);
            if (\is_array($cached)
                && isset($cached['recommendations'], $cached['analysis'], $cached['confidence'], $cached['reasoning'], $cached['metadata'])
                && \is_array($cached['recommendations'])
                && \is_array($cached['analysis'])
                && is_numeric($cached['confidence'])
                && \is_string($cached['reasoning'])
                && \is_array($cached['metadata'])
            ) {
                Log::debug('[TrainingOptimizationAgent] Using cached recommendations', [
                    'character_id' => $character->id,
                    'cache_key' => $cacheKey,
                ]);

                $cachedRecommendations = [];
                foreach ($cached['recommendations'] as $item) {
                    if (\is_array($item)) {
                        $cachedRecommendations[] = $item;
                    }
                }

                /** @var array<string, mixed> $cachedAnalysis */
                $cachedAnalysis = $cached['analysis'];
                /** @var array<string, mixed> $cachedMetadata */
                $cachedMetadata = $cached['metadata'];

                return [
                    'recommendations' => $cachedRecommendations,
                    'analysis' => $cachedAnalysis,
                    'confidence' => (float) $cached['confidence'],
                    'reasoning' => $cached['reasoning'],
                    'metadata' => $cachedMetadata,
                ];
            }

            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            // Parse and validate response
            $recommendations = $this->parseRecommendations($response, $trainingOptions);

            // Add metadata
            $recommendations['metadata'] = [
                'agent_id' => $this->agentId,
                'processing_time' => microtime(true) - $startTime,
                'character_id' => $character->id,
                'scenario_type' => $character->scenario_type,
                'mcp_server' => $this->getMCPServerName(),
            ];

            // Cache recommendations
            Cache::put($cacheKey, $recommendations, 300); // 5 minutes

            return $recommendations;
        } catch (\Exception $e) {
            Log::error('[TrainingOptimizationAgent] Analysis failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            // Return fallback recommendations
            return $this->getDefaultRecommendations($trainingOptions, $character);
        }
    }

    /**
     * Predict stat gains for specific training option
     *
     * @param  array<string, mixed>  $trainingOption
     * @return array{
     *     stat_gains: array<string, int>,
     *     energy_cost: int,
     *     failure_risk: float,
     *     spirit_burst_potential: float|null,
     *     skill_hints: array<int, string>,
     *     confidence: float
     * }
     */
    public function predictStatGains(
        Character $character,
        array $trainingOption
    ): array {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'training_option' => $trainingOption,
                'task' => 'predict_stat_gains',
            ];

            $response = $this->processWithMCPAgent($context);

            $statGains = [];
            if (isset($response['stat_gains']) && \is_array($response['stat_gains'])) {
                foreach ($response['stat_gains'] as $key => $value) {
                    if (\is_string($key) && is_scalar($value)) {
                        $statGains[$key] = (int) $value;
                    }
                }
            }
            $energyCost = isset($response['energy_cost']) && is_numeric($response['energy_cost'])
                ? (int) $response['energy_cost']
                : 0;
            $failureRisk = isset($response['failure_risk']) && is_numeric($response['failure_risk'])
                ? (float) $response['failure_risk']
                : 0.0;
            $spiritBurst = isset($response['spirit_burst_potential']) && is_numeric($response['spirit_burst_potential'])
                ? (float) $response['spirit_burst_potential']
                : null;
            $skillHints = [];
            if (isset($response['skill_hints']) && \is_array($response['skill_hints'])) {
                foreach ($response['skill_hints'] as $hint) {
                    if (is_scalar($hint)) {
                        $skillHints[] = (string) $hint;
                    }
                }
            }
            $confidence = isset($response['confidence']) && is_numeric($response['confidence'])
                ? (float) $response['confidence']
                : 0.8;

            return [
                'stat_gains' => $statGains,
                'energy_cost' => $energyCost,
                'failure_risk' => $failureRisk,
                'spirit_burst_potential' => $spiritBurst,
                'skill_hints' => $skillHints,
                'confidence' => $confidence,
            ];
        } catch (\Exception $e) {
            Log::error('[TrainingOptimizationAgent] Stat gain prediction failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return $this->getDefaultStatGainPrediction($trainingOption);
        }
    }

    /**
     * Optimize training sequence for multiple turns
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     sequence: array<int, array<string, mixed>>,
     *     expected_outcomes: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function optimizeTrainingSequence(
        Character $character,
        int $turns,
        array $goals = []
    ): array {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'turns' => $turns,
                'goals' => $goals,
                'task' => 'optimize_sequence',
            ];

            $response = $this->processWithMCPAgent($context);

            $sequence = [];
            if (isset($response['sequence']) && \is_array($response['sequence'])) {
                foreach ($response['sequence'] as $item) {
                    if (\is_array($item)) {
                        $sequence[] = $item;
                    }
                }
            }
            $expectedOutcomes = isset($response['expected_outcomes']) && \is_array($response['expected_outcomes'])
                ? $response['expected_outcomes']
                : [];
            $confidence = isset($response['confidence']) && is_numeric($response['confidence'])
                ? (float) $response['confidence']
                : 0.8;
            $reasoning = isset($response['reasoning']) && \is_string($response['reasoning'])
                ? $response['reasoning']
                : 'Optimized for goal achievement';

            return [
                'sequence' => $sequence,
                'expected_outcomes' => $expectedOutcomes,
                'confidence' => $confidence,
                'reasoning' => $reasoning,
            ];
        } catch (\Exception $e) {
            Log::error('[TrainingOptimizationAgent] Sequence optimization failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'sequence' => [],
                'expected_outcomes' => [],
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Unable to optimize sequence',
            ];
        }
    }

    /**
     * Prepare context for MCP agent
     *
     * @param  array<string, mixed>  $trainingOptions
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function prepareContext(
        Character $character,
        array $trainingOptions,
        array $goals
    ): array {
        return [
            'character' => $this->getCharacterData($character),
            'training_options' => $trainingOptions,
            'goals' => $goals,
            'scenario_type' => $character->scenario_type,
            'task' => 'analyze_training_options',
        ];
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
        // Check if MCP strands-agents server is available
        if (! $this->mcpClient->isStrandsAgentsAvailable()) {
            throw new \RuntimeException('MCP strands-agents server not available');
        }

        // TODO: Implement actual MCP agent call
        // This will use the strands-agents MCP server to create and invoke an agent
        // For now, return simulated response
        Log::debug('[TrainingOptimizationAgent] Processing with MCP agent', [
            'agent_id' => $this->agentId,
            'task' => $context['task'] ?? 'unknown',
        ]);

        // Simulated response structure
        return [
            'recommendations' => [],
            'analysis' => [],
            'confidence' => 0.85,
            'reasoning' => 'MCP agent analysis completed',
        ];
    }

    /**
     * Parse recommendations from MCP response
     *
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $trainingOptions
     * @return array{
     *     recommendations: array<int, array<string, mixed>>,
     *     analysis: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    protected function parseRecommendations(array $response, array $trainingOptions): array
    {
        $recommendations = [];
        if (isset($response['recommendations']) && \is_array($response['recommendations'])) {
            foreach ($response['recommendations'] as $item) {
                if (\is_array($item)) {
                    $recommendations[] = $item;
                }
            }
        }
        $analysis = isset($response['analysis']) && \is_array($response['analysis'])
            ? $response['analysis']
            : [];
        $confidence = isset($response['confidence']) && is_numeric($response['confidence'])
            ? (float) $response['confidence']
            : 0.8;
        $reasoning = isset($response['reasoning']) && \is_string($response['reasoning'])
            ? $response['reasoning']
            : 'Analysis completed';

        return [
            'recommendations' => $recommendations,
            'analysis' => $analysis,
            'confidence' => $confidence,
            'reasoning' => $reasoning,
        ];
    }

    /**
     * Get default recommendations when MCP is unavailable
     *
     * @param  array<string, mixed>  $trainingOptions
     * @return array{
     *     recommendations: array<int, array<string, mixed>>,
     *     analysis: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultRecommendations(array $trainingOptions, ?Character $character = null): array
    {
        $recommendations = [];
        $optionsList = is_array($trainingOptions['options'] ?? null)
            ? $trainingOptions['options']
            : $trainingOptions;

        $index = 0;
        foreach ($optionsList as $option) {
            if (! is_array($option)) {
                continue;
            }
            $recommendations[] = [
                'option_index' => $index,
                'priority' => 1.0 / ($index + 1),
                'reasoning' => 'Default recommendation',
            ];
            $index++;
        }

        return [
            'recommendations' => $recommendations,
            'analysis' => [
                'method' => 'fallback',
                'note' => 'MCP agent unavailable, using default recommendations',
            ],
            'confidence' => 0.5,
            'reasoning' => 'Fallback recommendations due to MCP unavailability',
            'metadata' => [
                'agent_id' => $this->agentId,
                'fallback' => true,
                'character_id' => $character?->id,
                'scenario_type' => $character?->scenario_type,
                'mcp_server' => $this->getMCPServerName(),
            ],
        ];
    }

    /**
     * Get default stat gain prediction
     *
     * @param  array<string, mixed>  $trainingOption
     * @return array{
     *     stat_gains: array<string, int>,
     *     energy_cost: int,
     *     failure_risk: float,
     *     spirit_burst_potential: float|null,
     *     skill_hints: array<int, string>,
     *     confidence: float
     * }
     */
    protected function getDefaultStatGainPrediction(array $trainingOption): array
    {
        return [
            'stat_gains' => [
                'speed' => 0,
                'stamina' => 0,
                'power' => 0,
                'guts' => 0,
                'wit' => 0,
            ],
            'energy_cost' => 20,
            'failure_risk' => 0.1,
            'spirit_burst_potential' => null,
            'skill_hints' => [],
            'confidence' => 0.5,
        ];
    }

    /**
     * Get cache key for recommendations
     *
     * @param  array<string, mixed>  $context
     */
    protected function getCacheKey(int $characterId, array $context): string
    {
        $contextHash = md5(json_encode($context) ?: '');

        return "training_optimization_{$characterId}_{$contextHash}";
    }

    /**
     * Get MCP server name
     */
    protected function getMCPServerName(): string
    {
        return 'strands-agents';
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
            'mcp_server' => $this->getMCPServerName(),
        ];
    }
}
